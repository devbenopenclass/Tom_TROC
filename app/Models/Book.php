<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Url;

// Modèle métier des livres : lecture/écriture en base,
// fallbacks de catalogue et helpers d'images / descriptions.
class Book extends Model
{
  // Retourne l'image d'une carte livre.
  // Priorité : mapping par titre, chemin image stocké, puis fallback global.
  public static function imagePath(?array $book, string $fallback = '/assets/img/figma/mask-group.png'): string
  {
    $image = trim((string)($book['image'] ?? ''));
    $titleFallback = self::fallbackImageByTitle((string)($book['title'] ?? ''));

    if ($titleFallback !== null) {
      return $titleFallback;
    }

    if ($image !== '') {
      $normalized = self::normalizeImagePath($image);
      if ($normalized !== null) {
        return $normalized;
      }
    }

    return $fallback;
  }

  // Certaines fiches détail utilisent un visuel différent de la carte.
  // Cette méthode permet de brancher une image dédiée par titre.
  public static function detailImagePath(?array $book, string $fallback = '/assets/img/figma/mask-group.png'): string
  {
    // Les fiches détail reprennent le même univers visuel que les cartes
    // du catalogue pour rester cohérentes avec la maquette "Single livre".
    return self::imagePath($book, $fallback);
  }

  // Détermine si une vignette doit afficher un badge de disponibilité.
  // Retourne la classe CSS et le libellé correspondant, ou null si aucun badge.
  public static function cardStatusBadge(?array $book): ?array
  {
    return match ((string)($book['status'] ?? 'available')) {
      'unavailable' => ['class' => 'book-status--off', 'label' => 'non dispo.'],
      'reserved' => ['class' => 'book-status--reserved', 'label' => 'réservé'],
      default => null,
    };
  }

  // Retourne le libellé lisible d'un statut livre.
  public static function statusLabel(?string $status): string
  {
    return match ((string)($status ?? 'available')) {
      'unavailable' => 'indisponible',
      'reserved' => 'réservé',
      default => 'disponible',
    };
  }

  // Retourne la variante visuelle du statut pour les pastilles de tableau.
  public static function statusPillClass(?string $status): string
  {
    return match ((string)($status ?? 'available')) {
      'available' => 'status-pill--ok',
      default => 'status-pill--off',
    };
  }

  // Récupère les derniers livres ajoutés pour la page d'accueil.
  // Si la base est vide ou indisponible, on tombe sur le catalogue démo.
  public static function latest(int $limit = 4): array
  {
    try {
      $stmt = self::db()->prepare("
        SELECT b.*, u.username
        FROM books b
        JOIN users u ON u.id = b.user_id
        WHERE " . User::activeSqlCondition('u') . "
        ORDER BY b.created_at DESC
        LIMIT {$limit}
      ");
      $stmt->execute();
      $books = $stmt->fetchAll();
      if (!empty($books)) {
        return $books;
      }
    } catch (\Throwable $e) {
    }

    return array_slice(self::fallbackCatalog(), 0, $limit);
  }

  // Retourne la liste publique des livres à l'échange,
  // avec filtre texte sur titre, auteur ou pseudo du membre.
  public static function exchangeList(?string $q = null): array
  {
    try {
      if ($q) {
        $stmt = self::db()->prepare("
          SELECT b.*, u.username
          FROM books b
          JOIN users u ON u.id = b.user_id
          WHERE " . User::activeSqlCondition('u') . "
            AND (b.title LIKE :q OR b.author LIKE :q OR u.username LIKE :q)
          ORDER BY b.created_at DESC
        ");
        $stmt->execute(['q' => "%{$q}%"]);
        $books = $stmt->fetchAll();
      } else {
        $stmt = self::db()->query("
          SELECT b.*, u.username
          FROM books b
          JOIN users u ON u.id = b.user_id
          WHERE " . User::activeSqlCondition('u') . "
          ORDER BY b.created_at DESC
        ");
        $books = $stmt->fetchAll();
      }

      if (!empty($books)) {
        return $books;
      }
    } catch (\Throwable $e) {
    }

    return self::filterFallbackCatalog($q);
  }

  // Charge une fiche livre unique.
  // En secours, on cherche aussi dans le catalogue de démonstration.
  public static function find(int $id): ?array
  {
    try {
      $stmt = self::db()->prepare("
        SELECT b.*, u.username
        FROM books b
        JOIN users u ON u.id = b.user_id
        WHERE b.id = :id
          AND " . User::activeSqlCondition('u') . "
        LIMIT 1
      ");
      $stmt->execute(['id' => $id]);
      $book = $stmt->fetch();
      if ($book) {
        return $book;
      }
    } catch (\Throwable $e) {
    }

    foreach (self::fallbackCatalog() as $book) {
      if ((int)$book['id'] === $id) {
        return $book;
      }
    }

    return null;
  }

  // Bibliothèque privée d'un utilisateur, utilisée dans "Mon compte" et les profils.
  public static function byUser(int $userId): array
  {
    $stmt = self::db()->prepare("SELECT * FROM books WHERE user_id = :uid ORDER BY created_at DESC");
    $stmt->execute(['uid' => $userId]);
    return $stmt->fetchAll();
  }

  // Création d'un livre depuis le formulaire membre.
  public static function create(array $data): int
  {
    $stmt = self::db()->prepare("
      INSERT INTO books (user_id, title, author, image, description, status)
      VALUES (:user_id, :title, :author, :image, :description, :status)
    ");
    $stmt->execute($data);
    return (int) self::db()->lastInsertId();
  }

  // Mise à jour sécurisée : seul le membre qui a ajouté le livre peut modifier sa ligne.
  public static function update(int $id, int $userId, array $data): void
  {
    $data['id'] = $id;
    $data['user_id'] = $userId;

    $stmt = self::db()->prepare("
      UPDATE books
      SET title=:title, author=:author, image=:image, description=:description, status=:status
      WHERE id=:id AND user_id=:user_id
    ");
    $stmt->execute($data);
  }

  // Suppression sécurisée : seulement pour le membre connecté qui a ajouté le livre.
  public static function delete(int $id, int $userId): void
  {
    $stmt = self::db()->prepare("DELETE FROM books WHERE id = :id AND user_id = :uid");
    $stmt->execute(['id' => $id, 'uid' => $userId]);
  }

  // Génère le texte long des fiches détail.
  // Les titres connus ont un texte dédié ; sinon on recycle la description
  // en base ou on génère un texte générique cohérent.
  public static function detailDescription(array $book): string
  {
    $description = trim((string)($book['description'] ?? ''));
    if ($description !== '') {
      return $description;
    }

    $map = [
      'esther' => "J'ai été immédiatement touché par l'atmosphère paisible d'Esther. Ce livre dégage une douceur rare, portée par des paysages calmes et une présence presque méditative.\n\nAu fil des pages, on découvre une oeuvre délicate, tournée vers l'introspection, la nature et les émotions discrètes. Tout y est simple, juste et profondément apaisant.\n\nC'est un livre que l'on ouvre pour ralentir, respirer et retrouver un peu de clarté. Il trouvera facilement sa place auprès des lecteurs en quête de beauté et de sérénité.\n\nEsther est une invitation à contempler autrement, avec plus de lenteur, plus d'attention, et beaucoup de sensibilité.",
      'thekinfolktable' => "J'ai récemment plongé dans les pages de 'The Kinfolk Table' et j'ai été enchanté par cette oeuvre captivante. Ce livre va bien au-delà d'une simple collection de recettes ; il célèbre l'art de partager des moments authentiques autour de la table.\n\nLes photographies magnifiques et le ton chaleureux captivent dès le départ, transportant le lecteur dans un voyage à travers des recettes et des histoires qui mettent en avant la beauté de la simplicité et de la convivialité.\n\nChaque page est une invitation à ralentir, à savourer et à créer des souvenirs durables avec les êtres chers.\n\n'The Kinfolk Table' incarne parfaitement l'esprit de la cuisine et de la camaraderie, et il est certain que ce livre trouvera une place spéciale dans le coeur de tout amoureux de la cuisine et des rencontres inspirantes.",
      'wabisabi' => "Wabi Sabi est un livre apaisant, subtil et profondément inspirant. Il propose une manière différente de regarder le monde, en valorisant l'imparfait, l'éphémère et la simplicité.\n\nSa lecture invite à ralentir et à retrouver de la beauté dans les petits détails du quotidien. On y découvre une philosophie douce, accessible, qui fait beaucoup de bien.\n\nLe texte, à la fois sensible et clair, accompagne parfaitement cette réflexion sur l'équilibre, l'acceptation et la présence à soi.\n\nC'est une lecture idéale pour celles et ceux qui aiment les livres contemplatifs, porteurs de calme et d'élégance.",
      'milkhoney' => "Milk and Honey est un recueil intense, direct et intime. Chaque texte touche à des thèmes universels comme l'amour, la perte, la reconstruction et la force intérieure.\n\nL'écriture de Rupi Kaur va à l'essentiel et laisse beaucoup de place à l'émotion. On lit ces pages comme on reçoit une confidence, avec simplicité mais avec impact.\n\nLe livre alterne fragilité et puissance, et c'est précisément ce contraste qui le rend si marquant.\n\nC'est une oeuvre qui parle au coeur, et qui accompagne souvent ses lecteurs bien après la dernière page.",
      'delight' => "Delight! est un livre plein d'énergie et de fraîcheur, qui attire immédiatement le regard. Il propose une approche vivante, positive et stimulante, avec une vraie personnalité visuelle.\n\nOn y trouve un ton enthousiaste, une construction dynamique et une envie constante de transmettre quelque chose de lumineux.\n\nC'est le genre de livre qui donne de l'élan, qui se feuillette avec plaisir et qui laisse une impression joyeuse.\n\nIl conviendra parfaitement aux lecteurs qui aiment les ouvrages inspirants, colorés et spontanés.",
      'milwaukeemission' => "Milwaukee Mission dégage une présence sobre et singulière. Son esthétique épurée attire d'abord l'oeil, puis laisse place à une lecture plus attentive, presque silencieuse.\n\nLe livre invite à se concentrer sur l'essentiel, avec une forme de retenue qui le rend particulièrement élégant.\n\nIl plaira à celles et ceux qui apprécient les objets éditoriaux soignés, minimalistes et raffinés.\n\nC'est une lecture qui se découvre avec calme, et dont le charme opère dans la durée.",
      'minimalistgraphics' => "Minimalist Graphics est une véritable source d'inspiration visuelle. Il met en avant la force des compositions simples, des contrastes justes et des choix graphiques assumés.\n\nChaque page donne envie de s'arrêter sur les détails, d'observer la structure, le rythme et l'équilibre des formes.\n\nC'est un livre qui nourrira autant la curiosité des passionnés de design que celle des lecteurs attirés par les beaux objets.\n\nOn en ressort avec une envie renouvelée de créer, d'épure et de clarté.",
      'hygge' => "Hygge est une lecture douce et réconfortante, pensée comme une parenthèse chaleureuse. Le livre explore avec simplicité cet art de vivre fait de confort, de convivialité et de petits plaisirs.\n\nLes pages donnent envie de ralentir, de se créer un intérieur apaisant et de savourer les instants les plus ordinaires.\n\nOn y retrouve une ambiance accueillante, lumineuse et profondément rassurante.\n\nC'est un ouvrage parfait pour les lecteurs qui aiment les atmosphères cocooning et les livres qui font du bien.",
      'innovation' => "Innovation est un livre stimulant, qui propose une réflexion claire et accessible sur la créativité, les idées neuves et la manière dont elles prennent forme.\n\nIl pousse à regarder autrement les projets, les méthodes de travail et les processus d'invention. On y trouve de nombreuses pistes pour penser plus librement.\n\nLe ton reste fluide, dynamique et concret, ce qui rend la lecture agréable du début à la fin.\n\nC'est un excellent choix pour les lecteurs curieux, les créatifs et tous ceux qui aiment les idées qui ouvrent des perspectives.",
      'psalms' => "Psalms se distingue par sa grande délicatesse visuelle et son atmosphère paisible. C'est un livre qui invite à l'apaisement, à la contemplation et à une forme de recentrage intérieur.\n\nSon esthétique soignée accompagne une lecture calme, presque méditative, qui prend le temps de laisser résonner les mots et les images.\n\nOn sent dans cet ouvrage une recherche d'harmonie, de lumière et de simplicité.\n\nIl touchera particulièrement les lecteurs sensibles aux livres spirituels, poétiques ou profondément apaisants.",
      'thinkingfastslow' => "Thinking, Fast & Slow est un ouvrage majeur pour comprendre la manière dont nous prenons nos décisions. Il explore avec clarté les mécanismes de la pensée, entre intuition rapide et raisonnement plus lent.\n\nLa lecture est riche, passionnante et souvent surprenante. On y découvre à quel point nos jugements sont influencés par des biais invisibles.\n\nDaniel Kahneman rend accessibles des notions complexes sans jamais perdre l'intérêt du lecteur.\n\nC'est un livre marquant, qui change durablement notre façon de réfléchir, d'analyser et d'observer le monde.",
      'abookfullofhope' => "A Book Full Of Hope est un livre sensible, lumineux et profondément réconfortant. Son ton sincère et délicat donne immédiatement l'impression d'être accompagné avec bienveillance.\n\nChaque page porte une forme de douceur, tout en gardant une vraie force émotionnelle. Le livre parle de résilience, d'élan et de confiance retrouvée.\n\nC'est une lecture qui fait du bien, sans lourdeur, avec une simplicité très touchante.\n\nIl conviendra parfaitement aux lecteurs qui cherchent un texte porteur d'espoir et de sens.",
      'thesubtleartofnotgivingafck' => "The Subtle Art Of Not Giving A F*ck propose un ton franc, direct et parfois provocateur, mais toujours au service d'une réflexion très concrète sur la vie, les priorités et l'énergie que l'on choisit de consacrer aux choses.\n\nLe livre bouscule avec humour, remet certaines évidences en question et pousse à se recentrer sur ce qui compte vraiment.\n\nSa lecture est rythmée, percutante et souvent libératrice.\n\nC'est un ouvrage idéal pour les lecteurs qui aiment les essais sans détour, efficaces et pleins de personnalité.",
      'narnia' => "Narnia est une invitation immédiate à l'évasion. Dès les premières pages, on entre dans un univers merveilleux, peuplé de symboles, d'aventures et de personnages inoubliables.\n\nLe livre mêle avec élégance imaginaire, émotion et quête initiatique. On y retrouve tout ce qui fait la magie des grands récits intemporels.\n\nSon pouvoir d'émerveillement reste intact, quel que soit l'âge du lecteur.\n\nC'est une oeuvre précieuse pour tous ceux qui aiment les mondes fantastiques, les légendes et les histoires qui laissent une trace durable.",
      'companyofone' => "Company Of One propose une réflexion très actuelle sur le travail, l'indépendance et la manière de construire un projet durable sans rechercher la croissance à tout prix.\n\nPaul Jarvis défend une approche plus libre, plus humaine et souvent plus intelligente de la réussite professionnelle. Le propos est clair, moderne et directement applicable.\n\nLa lecture pousse à remettre en question certains réflexes et ouvre des pistes concrètes pour travailler autrement.\n\nC'est un excellent livre pour les indépendants, les entrepreneurs ou tous ceux qui cherchent un modèle plus simple et plus cohérent.",
      'thetwotowers' => "The Two Towers prolonge l'aventure avec une intensité remarquable. On y retrouve toute la richesse de l'univers de Tolkien, entre tension dramatique, paysages grandioses et personnages profondément attachants.\n\nLe récit gagne en ampleur, en noirceur et en souffle épique, tout en conservant une grande finesse dans la construction du monde.\n\nChaque chapitre nourrit le sentiment d'être plongé dans une fresque immense, dense et inoubliable.\n\nC'est une lecture incontournable pour les amateurs de fantasy, de récits héroïques et d'univers magistralement bâtis.",
    ];

    $key = self::normalizeTitleKey((string)($book['title'] ?? ''));
    if (isset($map[$key])) {
      return $map[$key];
    }

    $title = trim((string)($book['title'] ?? 'Livre'));
    $author = trim((string)($book['author'] ?? 'Auteur inconnu'));
    $owner = trim((string)($book['username'] ?? 'un membre'));

    return sprintf(
      "%s est un livre proposé par %s et signé %s. Sa lecture offre une vraie personnalité, entre sensibilité, style et plaisir de découverte.\n\nChaque page invite à prendre le temps, à suivre une voix singulière et à profiter d'un ouvrage choisi avec soin.\n\nC'est un livre qui mérite d'être découvert, partagé et transmis à d'autres lecteurs.\n\nIl trouvera facilement sa place dans la bibliothèque de quelqu'un qui aime les lectures marquantes et les beaux livres.",
      $title,
      $owner,
      $author
    );
  }

  // Filtre le catalogue de secours en cas d'absence de base de données.
  private static function filterFallbackCatalog(?string $q = null): array
  {
    $books = self::fallbackCatalog();

    if (!$q) {
      return $books;
    }

    $needle = self::toLower($q);

    return array_values(array_filter($books, static function (array $book) use ($needle): bool {
      $haystack = self::toLower(
        ($book['title'] ?? '') . ' ' .
        ($book['author'] ?? '') . ' ' .
        ($book['username'] ?? '')
      );

      return str_contains($haystack, $needle);
    }));
  }

  // Bibliothèque factice utilisée pour garder le site présentable
  // même si les données locales sont absentes.
  private static function fallbackCatalog(): array
  {
    static $books = null;

    if ($books !== null) {
      return $books;
    }

    $books = [
      self::fallbackBook(9001, 301, 'Esther', 'Alabaster', 'CamilleDuCuir', 'available', '/assets/img/figma/latest-card-1.png'),
      self::fallbackBook(9002, 302, 'The Kinfolk Table', 'Nathan Williams', 'Nathalie', 'available', '/assets/img/figma/latest-card-2.png'),
      self::fallbackBook(9003, 303, 'Wabi Sabi', 'Beth Kempton', 'Alicecture', 'available', '/assets/img/figma/latest-card-3.png'),
      self::fallbackBook(9004, 304, 'Milk & honey', 'Rupi Kaur', 'Hugo1990_12', 'available', '/assets/img/figma/latest-card-4.png'),
      self::fallbackBook(9005, 305, 'Delight!', 'Justin Rossow', 'Juju1432', 'unavailable', '/assets/img/Card livre-1.png'),
      self::fallbackBook(9006, 306, 'Milwaukee Mission', 'Elder Cooper Low', 'Christiane75014', 'available', '/assets/img/Card livre-2.png'),
      self::fallbackBook(9007, 307, 'Minimalist Graphics', 'Julia Schonlau', 'Hamzalecture', 'available', '/assets/img/Card livre-3.png'),
      self::fallbackBook(9008, 308, 'Hygge', 'Meik Wiking', 'Hugo1990_12', 'available', '/assets/img/Card livre-4.png'),
      self::fallbackBook(9009, 309, 'Innovation', 'Matt Ridley', 'LouBen50', 'available', '/assets/img/Card livre-5.png'),
      self::fallbackBook(9010, 310, 'Psalms', 'Alabaster', 'Lolobzh', 'available', '/assets/img/Card livre-6.png'),
      self::fallbackBook(9011, 311, 'Thinking, Fast & Slow', 'Daniel Kahneman', 'Sas634', 'unavailable', '/assets/img/Card livre-7.png'),
      self::fallbackBook(9012, 312, 'A Book Full Of Hope', 'Rupi Kaur', 'ML95', 'available', '/assets/img/Card livre-8.png'),
      self::fallbackBook(9013, 313, 'The Subtle Art Of Not Giving A F*ck', 'Mark Manson', 'Verogo33', 'available', '/assets/img/Card livre-9.png'),
      self::fallbackBook(9014, 314, 'Narnia', 'C.S Lewis', 'AnnikaBrahms', 'unavailable', '/assets/img/Card livre-10.png'),
      self::fallbackBook(9015, 315, 'Company Of One', 'Paul Jarvis', 'Victoirefabr912', 'available', '/assets/img/Card livre-11.png'),
      self::fallbackBook(9016, 316, 'The Two Towers', 'J.R.R Tolkien', 'Lotrfanclub67', 'available', '/assets/img/Card livre-12.png'),
    ];

    return $books;
  }

  // Construit une entrée de livre de démonstration homogène.
  private static function fallbackBook(
    int $id,
    int $userId,
    string $title,
    string $author,
    string $username,
    string $status,
    string $image
  ): array {
    return [
      'id' => $id,
      'user_id' => $userId,
      'title' => $title,
      'author' => $author,
      'username' => $username,
      'status' => $status,
      'image' => $image,
      'description' => sprintf(
        '%s par %s, propose par %s dans la bibliotheque Tom Troc.',
        $title,
        $author,
        $username
      ),
      'created_at' => date('Y-m-d H:i:s'),
    ];
  }

  // Valide et normalise un chemin image venant de la base.
  // On accepte soit un asset déjà public, soit une image uploadée.
  private static function normalizeImagePath(string $image): ?string
  {
    if (preg_match('#^https?://#i', $image)) {
      return $image;
    }

    $path = '/' . ltrim($image, '/');
    if (str_starts_with($path, '/assets/')) {
      if (Url::publicFileExists($path)) {
        return $path;
      }
    }

    $uploadsPath = '/assets/uploads/' . ltrim(basename($image), '/');
    if (Url::publicFileExists($uploadsPath)) {
      return $uploadsPath;
    }

    return null;
  }

  // Associe certains titres connus à une couverture précise du projet.
  private static function fallbackImageByTitle(string $title): ?string
  {
    $map = [
      'esther' => '/assets/img/exchange-covers/esther.png',
      'thekinfolktable' => '/assets/img/exchange-covers/the-kinfolk-tablePL.png',
      'wabisabi' => '/assets/img/exchange-covers/wabi-sabi.png',
      'milkhoney' => '/assets/img/exchange-covers/milk-and-honey.png',
      'delight' => '/assets/img/exchange-covers/delight.png',
      'milwaukeemission' => '/assets/img/exchange-covers/milwaukee-mission.png',
      'minimalistgraphics' => '/assets/img/exchange-covers/minimalist-graphics.png',
      'hygge' => '/assets/img/exchange-covers/hygge.png',
      'innovation' => '/assets/img/exchange-covers/innovation.png',
      'psalms' => '/assets/img/exchange-covers/psalms.png',
      'thinkingfastslow' => '/assets/img/exchange-covers/thinking-fast-and-slow.png',
      'abookfullofhope' => '/assets/img/exchange-covers/a-book-full-of-hope.png',
      'thesubtleartofnotgivingafck' => '/assets/img/exchange-covers/the-subtle-art-of-not-giving-a-fck.png',
      'narnia' => '/assets/img/exchange-covers/narnia.png',
      'companyofone' => '/assets/img/exchange-covers/company-of-one.png',
      'thetwotowers' => '/assets/img/exchange-covers/the-two-towers.png',
    ];

    $key = self::normalizeTitleKey($title);
    return $map[$key] ?? null;
  }

  // Nettoie un titre pour créer une clé stable de matching.
  private static function normalizeTitleKey(string $value): string
  {
    $value = trim($value);
    $value = self::toLower($value);
    return preg_replace('/[^a-z0-9]+/i', '', $value);
  }

  // Compatibilité mbstring/non-mbstring pour normaliser la casse.
  private static function toLower(string $value): string
  {
    return function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
  }

  // Compatibilité mbstring/non-mbstring pour compter les caractères.
  private static function stringLength(string $value): int
  {
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
  }
}

<?php

namespace Src\Services;

/**
 * Classe pour gérer la protection CSRF (Cross-Site Request Forgery).
 * Elle génère un token CSRF sécurisé et le compare avec celui envoyé dans les requêtes POST.
 */
class ProfilePictureService
{
    private static $base_url = 'https://api.dicebear.com/9.x/identicon/svg';
    private array $parameters = [];
    private static $colors = [
        'light_red' => '#FADBD8',
        'light_green' => '#D5F5E3',
        'light_blue' => '#D6EAF8',
        'light_yellow' => '#FCF3CF',
        'light_cyan' => '#D1F2EB',
        'light_magenta' => '#F5EEF8',
        'light_gray' => '#EAEDED',
        'light_beige' => '#FDFEFE',
        'peach' => '#FDEBD0',
        'mint' => '#D4EFDF',
        'lavender' => '#E8DAEF',
        'powder_blue' => '#EBF5FB',
        'soft_pink' => '#FDEDEC',
        'soft_orange' => '#FDEBD0',
        'soft_purple' => '#EAECEE',
        'soft_teal' => '#D5F5E3',
        'ivory' => '#FDFEFE',
        'pale_gold' => '#FBF5E6',
        'pale_olive' => '#F9E79F',
        'pale_sky' => '#D6DBDF',
        'red' => '#E57373',
        'green' => '#81C784',
        'blue' => '#64B5F6',
        'yellow' => '#FFF176',
        'cyan' => '#4DD0E1',
        'magenta' => '#BA68C8',
        'gray' => '#BDBDBD',
        'beige' => '#F5F5DC',
        'orange' => '#FFB74D',
        'purple' => '#9575CD',
        'teal' => '#4DB6AC',
        'gold' => '#FFD54F',
        'olive' => '#AED581',
        'sky' => '#90CAF9',
    ];

    public static $preset_patterns = [
        'heart' => [
            'oxoxo',
            'xoxox',
            'xooox',
            'oxoxo',
            'ooxoo',
        ],
        'spade' => [
            'ooxoo',
            'oxxxo',
            'xxxxx',
            'ooxoo',
            'oxxxo',
        ],
        'diamond' => [
            'ooxoo',
            'oxoxo',
            'xooox',
            'oxoxo',
            'ooxoo',
        ],
        'hash' => [
            'oxoxo',
            'xxxxx',
            'oxoxo',
            'xxxxx',
            'oxoxo',
        ],
        'grid' => [
            'xoxox',
            'oxoxo',
            'xoxox',
            'oxoxo',
            'xoxox',
        ],
        'plus' => [
            'ooxoo',
            'ooxoo',
            'xxxxx',
            'ooxoo',
            'ooxoo',
        ],
        'tower' => [
            'xoxox',
            'xxxxx',
            'oxxxo',
            'oxxxo',
            'oxoxo',
        ],
        'crown' => [
            'xoxox',
            'xxxxx',
            'xxoxx',
            'xooox',
            'ooxoo',
        ],
        'helmet' => [
            'xxoxx',
            'xxxxx',
            'xoxox',
            'xooox',
            'xxoxx',
        ],
        'omega' => [
            'oxxxo',
            'xooox',
            'xooox',
            'oxoxo',
            'xooox',
        ],
        'coin' => [
            'oxxxo',
            'xxoxx',
            'xxoxx',
            'xxoxx',
            'oxxxo',
        ],
        'ship' => [
            'ooxoo',
            'oxxxo',
            'oxoxo',
            'xxoxx',
            'xoxox',
        ],
        'face' => [
            'xxoxx',
            'ooxoo',
            'ooxoo',
            'xooox',
            'oxxxo',
        ],
        'tree' => [
            'xxoxx',
            'oxxxo',
            'xoxox',
            'ooxoo',
            'oxxxo',
        ],
        'amogus' => [
            'oxxxo',
            'xooox',
            'xxxxx',
            'xxxxx',
            'xxoxx',
        ],
        'rabbit' => [
            'oxoxo',
            'oxoxo',
            'xoxox',
            'xxxxx',
            'oxoxo',
        ],
        'castle' => [
            'xoxox',
            'xxoxx',
            'oxxxo',
            'oxoxo',
            'xxxxx',
        ],
        'gladiator' => [
            'oxxxo',
            'xooox',
            'xxoxx',
            'xxoxx',
            'xxxxx',
        ],
    ];

    /**
     * Constructeur de la classe ProfilePicture.
     * 
     * @param string $seed La graine pour générer l'image de profil.
     * @param string $scale La taille de l'image de profil (par défaut 75).
     */
    public function __construct(?array $pattern = null, ?string $seed = null, string $scale = '75'){
        if ($pattern !== null) {
            $this->setPattern($pattern);
        }
        if ($seed !== null) {
            $this->addParameters('seed', $seed);
        } else {
            $this->setRandomPattern();
        }
        $this->addParameters('size', 100);
        $this->setScale($scale);
        $this->setRandomColor();
    }

    /**
     * Définit la taille de l'image de profil.
     * 
     * @param $scale La taille de l'image de profil.
     */
    public function setScale($scale): void{
        if (!is_numeric($scale) || $scale <= 0 || $scale > 200) {
            throw new \InvalidArgumentException('La taille doit être un nombre positif compris entre 1 et 200');
        }
        $this->addParameters('scale', $scale);
    }

    /**
     * Récupere l'URL de l'image de profil créée.
     * 
     * @return string L'URL de l'image de profil créée.
     */
    public function getProfilePicture(): string
    {
        return self::$base_url . '?' . http_build_query($this->parameters);
    }

    /**
     * Ajoute un paramètre à l'URL de l'image de profil.
     *
     * @param string $key La clé du paramètre.
     * @param string $value La valeur du paramètre.
     * @throws \InvalidArgumentException Si la clé ou la valeur n'est pas valide.
     */
    public function addParameters(string $key, string $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Définit le motif de l'image de profil.
     * 
     * @param array $pattern Un tableau contenant 5 lignes de motifs.
     * @throws \InvalidArgumentException Si le motif n'est pas valide.
     */
    public function setPattern(array $pattern): void
    {
        if (count($pattern) !== 5) {
            throw new \InvalidArgumentException('Le motif doit être un tableau contenant exactement 5 lignes');
        }
        foreach ($pattern as $key => $row) {
            if (!is_string($row) || (strlen($row) !== 5 && strlen($row) !== 3)) {
            throw new \InvalidArgumentException('Chaque ligne du motif doit être une chaîne contenant exactement 5 ou 3 caractères "x" ou "o"');
            }
            $this->setRow($key + 1, $row);
        }
    }

    /**
     * Définit une ligne du motif de l'image de profil.
     * Si la ligne contient 5 caractères, elle doit être symétrique.
     * Si la ligne contient 3 caractères, elle sera complétée par les 2 premiers caractères inversés.
     * 
     * @param int $row Le numéro de la ligne (0 à 5).
     * @param string $value La valeur de la ligne (une chaîne de 5 ou 3 caractères "x" ou "o").
     * @throws \InvalidArgumentException Si la ligne ou la valeur n'est pas valide.
     */
    public function setRow(int $row, string $value): void
    {
        if ($row < 0 || $row > 5) {
            throw new \InvalidArgumentException('La ligne doit être comprise entre 0 et 5');
        }
        if (!preg_match('/^[xo]{3,5}$/', $value)) {
            throw new \InvalidArgumentException('La valeur doit être une chaîne contenant uniquement "x" ou "o" avec une longueur de 3 ou 5');
        }
        if (strpos($value, 'x') === false) {
            throw new \InvalidArgumentException('La valeur doit contenir au moins un "x"');
        }
        if (strlen($value) === 5 && $value !== strrev($value)) {
            throw new \InvalidArgumentException('La valeur doit être symétrique si elle contient 5 caractères');
        }
        if (strlen($value) === 3) {
            $value .= strrev(substr($value, 0, 2));
        }
        $this->addParameters("row$row", $value);
    }

    /**
     * Définit la couleur de fond de l'image de profil.
     * 
     * @param string $color La couleur de fond (code hexadécimal).
     * @throws \InvalidArgumentException Si la couleur n'est pas valide.
     */
    public function setBackgroundColor(string $color): void
    {
        $this->addParameters('backgroundColor', $this->setColor($color));
    }
    
    /**
     * Définit la couleur des lignes de l'image de profil.
     * @param string $color La couleur des lignes (code hexadécimal).
     * @throws \InvalidArgumentException Si la couleur n'est pas valide.
     */
    public function setRowColor(string $color): void
    {
        $this->addParameters('rowColor', $this->setColor($color));
    }

    /**
     * Renvoi une couleur formattée
     * 
     * @param string $color La couleur (nom anglais ou code hexadécimal).
     * @return string La couleur formatée (code hexadécimal).
     * @throws \InvalidArgumentException Si la couleur n'est pas valide.
     */
    private function setColor(string $color): string
    {
        if (array_key_exists(strtolower($color), self::$colors)) {
            $color = self::$colors[strtolower($color)];
        } elseif (!preg_match('/^#?[0-9A-Fa-f]{6}$/', $color)) {
            throw new \InvalidArgumentException('La couleur doit être un nom anglais valide ou un code hexadécimal valide');
        }
        $color = ltrim($color, '#');
        return $color;
    }

    /**
     * Définit un motif aléatoire pour l'image de profil.
     * Chaque ligne est remplie avec un motif aléatoire parmi 'xoo', 'xxo', 'xxx'.
     */
    public function setRandomPattern(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $patterns = ['xoo', 'xxo', 'xxx'];
            $randomRow = str_shuffle($patterns[array_rand($patterns)]);
            $this->setRow($i, $randomRow);
        }
    }

    /**
     * Définit une couleur aléatoire pour l'image de profil.
     * La couleur de fond et la couleur des lignes sont choisies parmi les couleurs définies.
     * Elles ne doivent pas être identiques.
     */
    public function setRandomColor(): void
    {
        $randomColors = array_values(self::$colors);
        $color = $randomColors[array_rand($randomColors)];
        $backgroundColor = $randomColors[array_rand($randomColors)];
        while ($color === $backgroundColor) {
            $backgroundColor = $randomColors[array_rand($randomColors)];
        }
        $this->setBackgroundColor($backgroundColor);
        $this->setRowColor($color);
    }
}

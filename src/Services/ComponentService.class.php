<?php

namespace Src\Services;

/**
 * Classe qui gère la génération de composants HTML (boutons, cartes, tableaux).
 * Elle permet de créer facilement des éléments d'interface utilisateur réutilisables.
 */
class ComponentService
{
    /**
     * Fonction qui génère un bouton avec un lien, un texte et une classe CSS.
     * @param mixed $link
     * @param mixed $text
     * @param mixed $class
     * @return string
     */
    public static function generateButton(string $link, string $text, string $class = 'button primary', array $attributes = []): string
    {
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' . $attrString . '>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</a>';
    }

    /**
     * Fonction qui génère une carte avec un titre, un lien, un texte, une image et/ou un graphique.
     * @param mixed $title
     * @param mixed $link
     * @param mixed $text
     * @param mixed $img
     * @param mixed $chartId
     * @param mixed $chartType
     * @param mixed $labels
     * @param mixed $datasets
     * @param mixed $options
     * @return void
     */
    public static function generateCard($title, $link, $text = null, $img = null, $chartId = null, $chartType = null, $labels = [], $datasets = [], $options = [], $list = null): void
    {
        $defaultColors = [
            "#2B2D42",
            "#FF7F50",
            "#494C6F",
            "#FF5A1F",
            "#06149C",
            "#F4A300",
            "#8487AE",
            "#081DD9",
            "#FF6347",
            "#00CED1",
            "#8B008B",
            "#C71585"
        ];

        $isRaw = !isset($datasets[0]['label']);
        if ($isRaw) {
            $rawData = is_array($datasets) ? $datasets : [];
            $datasets = [[
                'label' => $title,
                'data'  => $rawData
            ]];
        }

        $perDataMode = $isRaw || in_array($chartType, ['pie', 'doughnut']);

        if ($perDataMode) {
            foreach ($datasets as &$dataset) {
                $countDs      = count($dataset['data']);
                $dsColors     = [];
                for ($i = 0; $i < $countDs; $i++) {
                    $dsColors[] = $defaultColors[$i % count($defaultColors)];
                }
                $dataset['backgroundColor'] ??= $dsColors;
                $dataset['borderColor']     ??= "#eeeeee";
                $dataset['borderWidth']     ??= 1;
            }
            unset($dataset);
        } else {
            $countDs   = count($datasets);
            $dsColors  = [];
            for ($i = 0; $i < $countDs; $i++) {
                $dsColors[] = $defaultColors[$i % count($defaultColors)];
            }
            foreach ($datasets as $i => &$dataset) {
                $dataset['backgroundColor'] ??= $dsColors[$i];
                $dataset['borderColor']     ??= "#eeeeee";
                $dataset['borderWidth']     ??= 1;
            }
            unset($dataset);
        }

        $options = array_replace_recursive([
            "layout" => [
                "padding" => [
                    "top"    => 5,
                    "bottom" => 0,
                    "left"   => 0,
                    "right"  => 0
                ]
            ]
        ], $options);

        // Construction de la carte
        $card = "<div class='card'>";
        $card .= "<div class='card-container'>";
        $card .= "<h2>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</h2>";

        if (!empty($text)) {
            $card .= "<p>" . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . "</p>";
        }
        if (is_array($list)) {
            $card .= "<ul class='card-list'>";
            foreach ($list as $item) {
                $name = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
                $progress = htmlspecialchars($item['progress'], ENT_QUOTES, 'UTF-8');

                $color = '#dc3545';
                if ($progress >= 70) $color = '#28a745';
                elseif ($progress >= 50) $color = '#ffc107';
                $card .= "<li><strong>$name</strong>";
                $card .= "<div class='progress-bar-container'>";
                $card .= "<div class='progress-bar' style='width: {$progress}%; background-color: $color;'></div>";
                $card .= "</div>";
                $card .= "<small>{$progress}%</small>";
                $card .= "</li>";
            }
            $card .= "</ul>";
        }
        if (!empty($img)) {
            $card .= "<img src='" . htmlspecialchars($img, ENT_QUOTES, 'UTF-8') . "' alt='Image illustrant " . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "'>";
        }

        if (!empty($chartId)) {
            $card .= "<canvas id='" . htmlspecialchars($chartId, ENT_QUOTES, 'UTF-8') . "'></canvas>";
            $card .= "<script>";
            $card .= "document.addEventListener('DOMContentLoaded', function() {";
            $card .= "try {";
            $card .= "var chartDataItem = {";
            $card .= "title: " . json_encode($title, JSON_THROW_ON_ERROR) . ",";
            $card .= "chartId: " . json_encode($chartId, JSON_THROW_ON_ERROR) . ",";
            $card .= "chartType: " . json_encode($chartType, JSON_THROW_ON_ERROR) . ",";
            $card .= "labels: " . json_encode($labels, JSON_THROW_ON_ERROR) . ",";
            $card .= "datasets: " . json_encode($datasets, JSON_THROW_ON_ERROR) . ",";
            $card .= "options: " . json_encode($options, JSON_THROW_ON_ERROR) . "";
            $card .= "};";
            $card .= "window.chartData = window.chartData || [];";
            $card .= "window.chartData.push(chartDataItem);";
            $card .= "} catch (e) {";
            $card .= "console.error(\"Erreur JSON dans generateCard : \", e);";
            $card .= "}";
            $card .= "});";
            $card .= "</script>";
        }
        $card .= "<div class='buttons'>";
        $card .= ComponentService::generateButton($link, "Voir Plus");
        $card .= "</div>";
        $card .= "</div>";
        $card .= "</div>";
        echo $card;
    }

    /**
     * Permet de générer un modal pour afficher les détails d'une ligne ou pour modifier une ligne.
     * @param mixed $modalId
     * @param mixed $type
     * @param mixed $row
     * @param mixed $isEdit
     * @return string
     */
    private static function generateModal($modalId, $type, $row, $isEdit = false)
    {
        // Modal de type Voir ou Modifier
        $modalContent = $isEdit ? self::generateEditForm($row) : self::generateViewContent($row);
        return "
        <div id='$modalId' class='modal'>
            <div class='modal-content'>
                <h2>$type</h2>
                <div class='modal-body'>
                    $modalContent 
                </div>
                <div class='modal-footer'>
                    <button class='close-modal'>Fermer</button>
                </div>
            </div>
        </div>
    ";
    }

    /**
     * Permet de générer un modal de vue pour une ligne donnée
     * @param mixed $row
     * @return string
     */
    private static function generateViewContent($row): string
    {
        $content = "<p>ID: {$row['id']}</p>";
        foreach ($row as $key => $value) {
            if ($key !== 'id') {
                $content .= "<p>$key: $value</p>";
            }
        }
        return $content;
    }

    /**
     * Permet de générer un formulaire d'édition pour une ligne donnée
     * @param mixed $row
     * @return string
     */
    private static function generateEditForm($row): string
    {
        $form = "<form id='edit-form-{$row['id']}'>";
        foreach ($row as $key => $value) {
            if ($key !== 'id') {
                $form .= "<label for='$key'>$key:</label><input type='text' id='$key' name='$key' value='$value'><br>";
            }
        }
        $form .= "<button type='submit'>Sauvegarder</button></form>";
        return $form;
    }

    /**
     * Permet de générer un modal de confirmation de suppression pour une ligne donnée
     * @param mixed $modalId
     * @param mixed $row
     * @return string
     */
    private static function generateDeleteModal($modalId, $row): string
    {
        return "
            <div id='{$modalId}' class='modal'>
                <div class='modal-content'>
                    <h2>Confirmer la suppression</h2>
                    <p>Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                    <div class='modal-footer'>
                        <button class='close-modal'>Annuler</button>
                        <button class='confirm-delete' data-id='{$row['id']}'>Oui</button>
                    </div>
                </div>
            </div>
        ";
    }
}

<?php

/**
 * Plugin Name: Links Block
 * Plugin URI: https://www.siatex.com
 * Description: To add multiple blocks of links by shortcode [linksblock col="3" hide="no" new-window="yes" row="2" n="10" mcol="1" tcol="2"]
 * Author: SiATEX
 * Author URI: https://www.siatex.com
 * Version: 1.0.1
 */

use sitemapGenerator\SitemapGenerator;

class LinksBlock
{
    private $attributes;

    public function __construct()
    {
        add_shortcode('linksblock', array($this, 'renderLinksBlock'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
        add_action('wp_ajax_linksblock_preview',  array($this, 'linksblock_preview'));
    }

    function linksblock_preview()
    {
        if (isset($_POST['shortcode'])) {
            //echo $_POST['shortcode'];
            echo do_shortcode(stripslashes(sanitize_text_field($_POST['shortcode'])));
        }
        wp_die();
    }
    public function enqueueStyles()
    {
        wp_enqueue_style('linksblock-style', plugin_dir_url(__FILE__) . 'style.css');
    }

    public function enqueueAdminScripts($hook_suffix)
    {
        if ($hook_suffix == 'toplevel_page_links-block-instructions') {
            wp_enqueue_style('linksblock-style', plugin_dir_url(__FILE__) . 'style.css');
            wp_enqueue_script('jquery');
            wp_enqueue_script('linksblock-admin-script', plugin_dir_url(__FILE__) . 'admin-script.js', array('jquery'), false, true);
        }
    }

    public function renderLinksBlock($atts)
    {
        $this->attributes = shortcode_atts(array(
            'col' => 3,
            'hide' => 'no',
            'new-window' => 'yes',
            'row' => 2,
            'rand' => 'yes',
            'n' => 60,
            'mcol' => 1,
            'tcol' => 2,
            'single-line' => 'yes'
        ), $atts);

        return $this->generateBlocks();
    }

    public function addAdminMenu()
    {
        add_menu_page(
            'Links Block Instructions',
            'Links Block',
            'manage_options',
            'links-block-instructions',
            array($this, 'renderAdminPage'),
            'dashicons-editor-help'
        );
    }

    public function renderAdminPage()
    {
?>
        <style>
            #shortcode-preview {
                background: #fff;
                padding: 28px;
            }

            #shortcode-preview .col {
                padding: 0 20px;
            }
        </style>
        <div class="wrap">
            <h1>Links Block Shortcode Instructions</h1>
            <p>Use the shortcode <code>[linksblock]</code> with the following attributes:</p>
            <form id="shortcode-builder">
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Attribute</th>
                            <th>Build</th>
                            <th>Description</th>
                            <th>Default</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>col</code></td>
                            <td><input type="number" id="col" name="col" value="3" /></td>
                            <td>Number of columns</td>
                            <td>3</td>
                        </tr>
                        <tr>
                            <td><code>hide</code></td>
                            <td>
                                <select id="hide" name="hide">
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </td>
                            <td>Initial visible status of all blocks (yes/no)</td>
                            <td>no</td>
                        </tr>
                        <tr>
                            <td><code>new-window</code></td>
                            <td><select id="new-window" name="new-window">
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select></td>
                            <td>Open links in a new window (yes/no)</td>
                            <td>yes</td>
                        </tr>
                        <tr>
                            <td><code>row</code></td>
                            <td><input type="number" id="row" name="row" value="2" /></td>
                            <td>Number of rows</td>
                            <td>2</td>
                        </tr>
                        <tr>
                            <td><code>rand</code></td>
                            <td><select id="rand" name="rand">
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select></td>
                            <td>Shuffle links (yes/no)</td>
                            <td>yes</td>
                        </tr>
                        <tr>
                            <td><code>n</code></td>
                            <td><input type="number" id="n" name="n" step="5" value="60" /></td>
                            <td>Total number of links</td>
                            <td>60</td>
                        </tr>
                        <tr>
                            <td><code>mcol</code></td>
                            <td><input type="number" id="mcol" name="mcol" value="1" /></td>
                            <td>Maximum columns on small devices</td>
                            <td>1</td>
                        </tr>
                        <tr>
                            <td><code>tcol</code></td>
                            <td><input type="number" id="tcol" name="tcol" value="2" /></td>
                            <td>Maximum columns on tablets</td>
                            <td>2</td>
                        </tr>
                        <tr>
                            <td><code>single-line</code></td>
                            <td><select id="single-line" name="single-line">
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select></td>
                            <td>Display links in a single line (yes/no)</td>
                            <td>yes</td>
                        </tr>
                    </tbody>
                </table>
                <h3>Generated Shortcode</h3>
                <p><code id="generated-shortcode">[linksblock col="3" hide="no" new-window="yes" row="2" rand="yes" n="60" mcol="1" tcol="2" single-line="yes"]</code></p>
            </form>
            <h3>Preview</h3>
            <div id="shortcode-preview"></div>
        </div>
<?php
    }

    private function getAllLinks()
    {
        if (class_exists('\sitemapGenerator\SitemapGenerator')) {
            return SitemapGenerator::allLinks();
        }
        // Example implementation, replace with actual logic to get all links
        return array();
    }

    private function colCalc($str = "box-", $n = 4)
    {
        $class = "";
        if (!empty($n)) {
            $colW = 12 / $n;
            if (is_float($colW)) {
                $colW = number_format($colW, 1);
                $colW = str_replace(".", "-", $colW);
            }
            $class = " $str$colW";
        }
        return $class;
    }

    private function generateBlocks()
    {
        // All Links
        $getAllLinks = $this->getAllLinks();
        if ($this->attributes['rand'] == "yes") {
            shuffle($getAllLinks);
        }
        $links = array_slice($getAllLinks, 0, $this->attributes['n']);

        $class = "col links-block";
        // Column For Mobile
        $class .= $this->colCalc("m", $this->attributes["mcol"]);
        $class .= $this->colCalc("t", $this->attributes["tcol"]);
        $class .= $this->colCalc("col-", $this->attributes["col"]);

        $totalLinks = count($links);
        $maxCol = intval($this->attributes["col"] * $this->attributes["row"]);

        // Calculate how many links will be in each chunk
        $linksPerChunk = floor($totalLinks / $maxCol);
        $remainingLinks = $totalLinks % $maxCol;

        // Initialize array to hold the chunks
        $arrayParts = [];
        // Add the chunks with calculated items
        $startIndex = 0;
        for ($i = 0; $i < $maxCol; $i++) {
            $chunkSize = $linksPerChunk + ($remainingLinks > 0 ? 1 : 0);
            $arrayParts[] = array_slice($links, $startIndex, $chunkSize);
            $startIndex += $chunkSize;
            $remainingLinks--;
        }

        $hide = $this->attributes['hide'] == "yes" ? "link-block-collapsable" : "";
        if ($hide == "") {
            $html = "<div class=\"row $hide\">";
        } else {
            $html = "<div class='linkblock-trigger-area'><span onclick=\"linkblockswrapper.classList.toggle('show')\"><span class='arrow-bottom-clip'></span></span></div>";
            $html .= "<div id='linkblockswrapper' class=\"row $hide\">";
        }
        foreach ($arrayParts as $part) {
            $html .= "<div class=\"$class\"><ul class='link-list'>";
            foreach ($part as $link) {
                $lastPart = basename($link);
                $lastPart = pathinfo($lastPart, PATHINFO_FILENAME);
                $lastPart = str_replace(['-', "_"], ' ', $lastPart);
                $lastPart = ucwords($lastPart);
                $link = str_replace("_", "/", $link);
                $singleLine = $this->attributes['single-line'] == 'yes' ? "single-line" : "";
                $newWindow = $this->attributes['new-window'] == 'yes' ? 'target="_blank"' : "";
                $html .= "<li class='link-item'><a $newWindow class=\"$singleLine\" href=\"$link\">$lastPart</a></li>";
            }
            $html .= "</ul></div>";
        }
        $html .= "</div>";

        return $html;
    }
}

// Initialize the class
new LinksBlock();

?>
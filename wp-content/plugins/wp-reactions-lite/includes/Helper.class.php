<?php
namespace WP_Reactions\Lite;

class Helper
{

    static function getTemplate($name, $data = array(), $once = false)
    {
        global $wpra_lite;
        $file = WPRA_LITE_PLUGIN_PATH . '/' . $name . '.php';
        if ($once) {
            require_once($file);
        } else {
            require($file);
        }
    }

    static function getOptionBlock($name, $data = array())
    {
        global $wpra_lite;
        $options = ($_GET['page'] == 'wpra-global-options') ? Configuration::$current_options : Configuration::$default_options;
        $file = WPRA_LITE_PLUGIN_PATH . '/view/admin/option-blocks/' . $name . '.php';
        require($file);
    }

    static function getSocialIcon($name, $color)
    {
        $file = WPRA_LITE_PLUGIN_PATH . '/view/admin/social-icons/' . $name . '.php';
        require($file);
    }

    static function getAsset($name, $nocache = false)
    {
        $v = '?v=' . WPRA_LITE_VERSION;
        return WPRA_LITE_PLUGIN_URL . 'assets/' . $name . $v;
    }

    static function printStaticEmoji($id)
    {
        echo file_get_contents( WPRA_LITE_PLUGIN_PATH . 'assets/emojis/svg/' . $id . '.svg');
    }

    static function pixels($start, $step = 1)
    {
        $wpj_sizes = [];
        for ($i = $start; $i <= 60; $i += $step) {
            $wpj_sizes[$i . 'px'] = $i . 'px';
        }

        return $wpj_sizes;
    }

    static function array_merge_if_empty($arr)
    {
        $res = array();
        foreach ($arr as $elem) {
            $res[] = empty($elem) ? 0 : $elem;
        }

        return $res;
    }

    static function is($page)
    {
        if (strpos($_GET['page'], $page) === false) {
            return false;
        }

        return true;
    }

    static function getAdminPage($page)
    {
        $pages = array(
            'global' => 'wpra-global-options',
            'pro' => 'wpra-pro',
            'support' => 'wpra-support',
        );

        return admin_url('admin.php?page=' . $pages[$page]);
    }


    static function is_disabled($val, $check = 'true')
    {
        if ($val != $check) {
            echo ' disabled ';
        } else {
            echo '';
        }
    }

    static function array_rand_value($arr)
    {
        return $arr[array_rand($arr)];
    }

    static function tooltip($content)
    {
        ?>
        <div class="wpra-tooltip">
            <span class="wpra-tooltip-icon"
                  style="background-image: url('<?php echo Helper::getAsset('images/tooltip_icon.svg'); ?>')"></span>
            <div class="wpra-tooltip-content-wrap">
                <div class="wpra-tooltip-content">
                    <?php self::getTemplate('view/admin/tooltips/' . $content); ?>
                </div>
            </div>
        </div>
        <?php
    }

    static function guide($title, $content) {
        ?>
        <div class="wpra-guide-box" id="wpra-<?php echo $content; ?>">
            <div class="wpra-guide-box-header">
                <i class="dashicons dashicons-flag"></i>
                <span><?php echo $title; ?></span>
            </div>
            <div class="wpra-guide-box-content">
                <?php self::getTemplate('view/admin/guides/' . $content); ?>
                <span class="wpra-guide-box-next">Next Tip ></span>
            </div>
        </div>
        <?php
    }

    static function formatCount($count)
    {
        $count = intval($count);
        $format = $count;
        if ($count >= 1000000) {
            $format = round(($count / 1000000), 1) . 'm';
        } elseif ($count >= 1000) {
            $format = round(($count / 1000), 1) . 'k';
        }

        return $format;
    }

}

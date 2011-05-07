<?php
/**
 * Project:     TwoMice CiviCRM modifications and custom features (for client: GPF)
 * $Id$
 */

/**
 * Drupal module file.
 *
 * @package TM_CiviCRM
 *
 */

define ('TM_ERROR_FATAL', 1);
define ('TM_ERROR_ERROR', 2);
define ('TM_ERROR_WARNING', 4);
define ('TM_ERROR_DEBUG', 8);
define ('TM_ERROR_DENIED', 16);

class TM_Util {

    public static function trigger_error($msg, $code = NULL, $repeat = TRUE, $adminDetails = NULL) {
        global $user;
        if ($user->uid == 1 || variable_get('tmcivi_user_debug', 0)) {
            // for the super-user only, or when debug messages are enabled:
            // add line and file information to $msg
            $trace = debug_backtrace();
            if ($adminDetails) $msg .= "<br>&nbsp; &nbsp; <em>Details: $adminDetails</em>";
            $msg .= "<br>&nbsp; &nbsp; Reported in {$trace[0]['file']}, line {$trace[0]['line']}.";
        }

        switch ($code) {
            case TM_ERROR_FATAL:
                drupal_set_message($msg, 'error', $repeat);
                // "Fatal" just means we give up and go back to Drupal, not that the whole app must die.
                drupal_goto('<front>');
            break;

            case TM_ERROR_ERROR:
                drupal_set_message($msg, 'error', $repeat);
            break;

            case TM_ERROR_WARNING:
                drupal_set_message($msg, 'warning', $repeat);
            break;

            case TM_ERROR_DEBUG:
                if (variable_get('tmcivi_user_debug', 0) || ( $user->uid == 1 && variable_get('tmcivi_admin_debug', 0) )) {
                    drupal_set_message("DEBUG MESSAGE: $msg", 'warning', $repeat);
                }
            break;

            case TM_ERROR_DENIED:
                global $user;
                if (!$user->uid) {
                    $msg .= " <p>You may need to log in to access this content.</p>";
                }
                drupal_set_message($msg, 'error', $repeat);
                CRM_Utils_System::permissionDenied( );
                exit();
            break;

        }
    }

    public static function set_session_var($var, $value) {
        $_SESSION['tmcivi'][$var] = $value;
    }

    public static function get_session_var($var, $default = NULL) {
        if ( isset( $_SESSION['tmcivi'][$var] ) ) {
            return $_SESSION['tmcivi'][$var];
        } else {
            return $default;
        }
    }

    public static function clear_session_vars() {
        unset($_SESSION['tmcivi']);
    }

    /** Build breadcrumb array based on action properties from Action Registry, as given
     */
    public static function build_action_breadcrumb($properties) {
        $bc = $properties['breadcrumb'];

        if (FALSE !== strpos($bc, '|') || FALSE !== strpos($bc, '/%')) {
        // Any breadcrumb separated with pipes is a set of drupal paths

            $crumbs = explode('|', $bc);

            foreach ($crumbs as $crumb) {
                if (empty($crumb)) {
                    // skip any empty crumbs
                    continue;
                }
                if (FALSE !== strpos($crumb, '/%')) {
                /* breadcrumb components that start with % are function names; run the function to get the value */
                    $parts = explode('/', $crumb);
                    $i = 0;
                    foreach ($parts as $part) {
                        if (strpos($part, '%') === 0) {
                            require_once('TM/inc/breadcrumb_functions.php');
                            $functionName = 'bc_'. substr($part, 1);
                            if (function_exists($functionName)) {
                                $parts[$i] = call_user_func($functionName);
                            } else {
                                TM_Util::trigger_error('Bad %functionname in action breadcrumb.', TM_ERROR_ERROR);
                            }
                        }
                        $i++;
                    }
                    $crumb = implode('/', $parts);
                }
                if ($crumb == '<front>') {
                    $breadcrumb[] = array (
                        'title' => 'Home',
                        'url'   => base_path(),
                    );
                } else {
                    $item = menu_get_item($crumb);
                    $breadcrumb[] = array (
                        'title' => $item['title'],
                        'url'   => base_path() . $item['href'],
                    );
                }
            }
        } elseif (FALSE !== strpos($bc, 'tm:'))  {
        /* any breadcrumb starting with "tm:" is a reference to another action. Use that action's
           breadcrumb and then append that action itself. */
            $registry = TM_Core_ActionRegistry::get();
            $props = $registry->get_action_properties(substr($bc, 3));
            $breadcrumb = call_user_func(array(__CLASS__, __METHOD__), $props);
            $breadcrumb[] = TM_Util::make_action_breadcrumb($props);
        } else {
        /* Otherwise, this breadcrumb is a CiviCRM path. Use civi functions to parse it out. */
            $menuItem = CRM_Core_Menu::get($bc);
            $breadcrumb = $menuItem['breadcrumb'];
        }
        return (array)$breadcrumb;
    }

    static function make_action_breadcrumb ($properties) {

        $type = ( $properties['is_form'] ? 'form' : 'page' );
        return array (
            'title' => $properties['title'],
                'url'   => url(TM_ROOT_URL ."/{$type}", array('query' => "tmref={$properties['template']}")),
        );
    }

    /**
     * Add css file to global array of included css files, or just get the array
     * @staticvar array $files
     * @param string $op Either 'get' or 'add'
     * @param string $file If $op=='get', this is a string containing a URL (absolute, or relative to index.php) to the file.
     *      No checking is performed as to the validity of this path.
     * @return array Array of files
     */
    static function css($op = 'get', $file = NULL) {
        static $files = array();

        if ($op == 'add') {
            $files[] = $file;
        }

        return $files;
    }

    static function unpackValues($string = '') {
        $string = trim($string, CRM_Core_DAO::VALUE_SEPARATOR);
        if ( empty( $string ) ) {
            $array = array( );
        } else {
            $array = array_unique( explode( CRM_Core_DAO::VALUE_SEPARATOR, $string ) );
        }
        return $array;
    }

    static function packValues($array) {
        return CRM_Core_DAO::VALUE_SEPARATOR . implode( CRM_Core_DAO::VALUE_SEPARATOR, array_unique( $array ) ) . CRM_Core_DAO::VALUE_SEPARATOR;
    }

    /**
     * Stream the given data directly to output
     * 
     * @param string $data The data to output (e.g., data returned from file_get_contents(), or similar)
     * @param array $options Array of key/value pairs to control streaming behavior. Valid options are:
     *           'filename'  (you probably shouldn't skip this)
     *           'Content-type' (defaults to "application/octet-stream")
     *           'delete' if STRING, file with path/name STRING will be unlink()'d after streaming.
     * 
     * @return NULL exits, does not return.
     */
    static function stream( $data, $options = array( ) ) {

        $size = strlen($data);

        $contentType = ( $options['Content-type'] ? $options['Content-type'] : "application/octet-stream" );

        /* send the correct headers
         */
        // Remove "no-cache" headers - required for IE on https connections
        header("Cache-Control: ");
        header("Pragma: ");

        // Send headers for the download itself
        header("Content-type: {$contentType}; name=\"{$options['filename']}\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: $size");
        header("Content-Disposition: attachment; filename=\"{$options['filename']}\"");
        header("Expires: 0");
        print $data;

        if ( $options['delete'] ) {
            unlink( $options['delete'] );
        }
        exit;

        
    }

}
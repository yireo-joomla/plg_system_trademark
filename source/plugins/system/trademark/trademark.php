<?php
/**
 * Joomla! plugin Trademark
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2013 Yireo.com. All rights reserved
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die();

// Import the parent class
jimport( 'joomla.plugin.plugin' );

/**
 * Trademark Content Plugin
 *
 * @package		Joomla
 * @subpackage	Content
 */
class plgSystemTrademark extends JPlugin
{
    /**
     * Event onAfterRender
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterRender()
    {
        // Determine whether to use in the frontend or backend
        $application = JFactory::getApplication();
        if (!$application->isSite()) return false;
        if (JRequest::getCmd('tmpl') == 'component') return false;

        // Get the plugin-parameters
 	    $pluginParams = $this->getParams();

        $trademarks = explode( ',', $pluginParams->get('trademarks', '')) ;
        $article_id = $pluginParams->get('article_id', 0) ;
        $article_link = $pluginParams->get('article_link', 0) ;
        $occurrances = $pluginParams->get('occurrances', -1) ;

        if ($article_link == 1 && $article_id > 0) {
            include_once JPATH_SITE.'/components/com_content/helpers/route.php';
            $article_url = ContentHelperRoute::getArticleRoute($article_id);
            $tm = '<sup><a href="'.$article_url.'" title="'.JText::_('Trademarks').'">TM</a></sup>' ;
        } else {
            $tm = '<sup>TM</sup>' ;
        }

        // We have trademarks
        if (!empty($trademarks)) {

            // Get the body and fetch a list of files
            $html = JResponse::getBody();
            preg_match("/<body.*\/body>/s", $html, $matches);
            $htmlBody = $matches[0];
            $originalHtmlBody = $htmlBody;

            foreach ($trademarks as $trademark) {
                $trademark = trim($trademark);
                $t = preg_quote($trademark);
                $search = '/>([^>]+)([\W]+)('.$t.')([\W]+)/';
                $replace = '>\1\2\3'.$tm.'\4' ;
                if($trademark != '') {
                    $htmlBody = preg_replace($search, $replace, $htmlBody, $occurrances);
                }
            }

            $html = str_replace($originalHtmlBody, $htmlBody, $html);
            JResponse::setBody($html);
        }
        
        return true;
	}

    /**
     * Load the parameters
     *
     * @access private
     * @param null
     * @return JParameter
     */
    private function getParams()
    {
        jimport('joomla.version');
        $version = new JVersion();
        if(version_compare($version->RELEASE, '1.5', 'eq')) {
            $plugin = JPluginHelper::getPlugin('system', 'trademark');
            $params = new JParameter($plugin->params);
            return $params;
        } else {
            return $this->params;
        }
    }
}

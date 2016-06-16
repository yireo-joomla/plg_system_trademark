<?php
/**
 * Joomla! plugin Trademark
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2015 Yireo.com. All rights reserved
 * @license   GNU Public License
 * @link      http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the parent class
jimport('joomla.plugin.plugin');

/**
 * Trademark Content Plugin
 */
class PlgSystemTrademark extends JPlugin
{
	/**
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * Event onAfterRender
	 */
	public function onAfterRender()
	{
		if ($this->allowTrademarkReplacement() == false)
		{
			return false;
		}

		$trademarks  = explode(',', $this->params->get('trademarks', ''));
		$occurrances = $this->params->get('occurrances', -1);

		$tm = $this->getTrademarkText();

		// We have trademarks
		if (!empty($trademarks))
		{
			// Get the body and fetch a list of files
			$html = $this->app->getBody();
			preg_match("/<body.*\/body>/s", $html, $matches);
			$htmlBody         = $matches[0];
			$originalHtmlBody = $htmlBody;

			foreach ($trademarks as $trademark)
			{
				$trademark = trim($trademark);
				$t         = preg_quote($trademark);
				$search    = '/>([^>]+)([\W]+)(' . $t . ')([\W]+)/';
				$replace   = '>\1\2\3' . $tm . '\4';

				if ($trademark != '')
				{
					$htmlBody = preg_replace($search, $replace, $htmlBody, $occurrances);
				}
			}

			$html = str_replace($originalHtmlBody, $htmlBody, $html);
			$this->app->setBody($html);
		}

		return true;
	}

	/**
	 * @return bool
	 */
	protected function allowTrademarkReplacement()
	{
		// Determine whether to use in the frontend or backend
		if (!$this->app->isSite())
		{
			return false;
		}

		$input = $this->app->input;

		if ($input->getCmd('tmpl') == 'component')
		{
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	protected function getTrademarkText()
	{
		$article_url  = $this->getArticleUrl();

		if (!$article_url)
		{
			return '<sup>TM</sup>';
		}

		$tm = '<sup><a href="' . $article_url . '" title="' . JText::_('Trademarks') . '">TM</a></sup>';

		return $tm;
	}

	/**
	 * @return string|false
	 */
	protected function getArticleUrl()
	{
		$article_link = (int) $this->params->get('article_link', 0);

		if ($article_link == 0)
		{
			return false;
		}

		$article_id = (int) $this->params->get('article_id', 0);

		if (!$article_id > 0)
		{
			return false;
		}

		include_once JPATH_SITE . '/components/com_content/helpers/route.php';
		$article_url = ContentHelperRoute::getArticleRoute($article_id);

		return $article_url;
	}
}

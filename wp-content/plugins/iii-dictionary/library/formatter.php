<?php
/*
 * Dictionary's words formatter class
 */

class WFormatter {

	/*
	 * format headword
	 */
	public static function _hw($str)
	{
		$str = str_replace('*', html_entity_decode('<b>&#x2027;</b>', ENT_COMPAT, 'UTF-8'), $str);
		return preg_replace('/\{.*?\}/', '', $str);
	}

	// format inflected form
	public static function _inflection($str, $dictionary)
	{
		$str = json_decode($str);

		if(is_null($str) || $str == 'null' || empty($str)) {
			return false;
		}

		$dom = new DOMDocument;

		foreach($str as $item)
		{
			$hw = $sound = $pronunciation = '';
			@$dom->loadHTML($item);

			$if_tag = $dom->getElementsByTagName('if');
			if($if_tag->length > 0) {
				$hw = WFormatter::_hw($if_tag->item(0)->nodeValue);
			}

			$wav_tag = $dom->getElementsByTagName('wav');
			if($wav_tag->length > 0) {
				$sound = WFormatter::_sound($wav_tag, $dictionary);
			}

			$pr_tag = $dom->getElementsByTagName('pr');
			if($pr_tag->length > 0) {
				$pronunciation = WFormatter::_pr($pr_tag);
			}

			$output[] = $hw . ' ' . $sound . ' <span class="pr">' . $pronunciation . '</span>';
		}

		return implode('; ', $output);
	}

	/*
	 * get Label <lb>
	 */
	public static function _label($word, $dictionary)
	{
		$str = json_decode($word->label);

		if(is_null($str)) {
			return false;
		}

		return WFormatter::specialchars_to_html($str[0]);
	}

	/*
	 * get Variant spelling <vr>
	 */
	public static function _variant($word, $dictionary)
	{
		$variant = json_decode($word->variant);

		if(is_null($variant)) {
			return false;
		}

		$dom = new DOMDocument;
		@$dom->loadXML('<vr>' . $variant[0] . '</vr>');

		$html = '';
		foreach($dom->documentElement->childNodes as $node)
		{
			if($node->nodeName == 'vl') {
				$html .= ' <em>' . $node->nodeValue . '</em>';
			}
			if($node->nodeName == 'va') {
				$html .= ' <span class="va">' . WFormatter::_hw($node->nodeValue) . '</span>';
			}
			if($node->nodeName == 'sound') {
				$html .= ' ' . WFormatter::_sound($node->nodeValue, $dictionary);
			}
			if($node->nodeName == 'pr') {
				$html .= WFormatter::_pr($node->nodeValue);
			}
			if($node->nodeName == 'altpr') {
				$html .= WFormatter::_pr(json_encode(array($node->nodeValue)));
			}
		}

		return $html;
	}

	/*
	 * get Pronunciation
	 */
	public static function _sound($str, $dictionary, $url_only = false)
	{
		if(is_object($str)) {
			$wav = $str->item(0)->nodeValue;
		}
		else {
			$sound = json_decode($str);
			$wav = empty($sound) ? $str : $sound->wav;
		}

		if($wav != '')
		{
			$file_name = substr($wav, 0, strpos($wav, '.wav'));

			if($dictionary == 'elearner' || $dictionary == 1)
			{
				$url = 'http://mwd.s3.amazonaws.com/dictsounds/leaners/' . $file_name . '.mp3';

				// if file not found, try using uppercase file name
				/* if(curl_get_file_size($url) == -1) {
					$url = 'http://mwd.s3.amazonaws.com/dictsounds/leaners/' . strtoupper($file_name) . '.mp3';
					
					
				} */
				
				// if still not found, try collegiate source
				if(curl_get_file_size($url) == -1) {
					$url = 'http://mwd.s3.amazonaws.com/dictsounds/collegiate/' . substr($wav, 0, 1) . '/' . $file_name . '.mp3';
				}
			}
			else if($dictionary == 'medical' || $dictionary == 3)
			{
				$url = 'http://mwd.s3.amazonaws.com/dictsounds/medical_new/' . $file_name . '.mp3';
				
				// if file not found, try using uppercase file name
				/* if(WFormatter::curl_get_file_size($url) == -1) {
					$url = 'http://mwd.s3.amazonaws.com/dictsounds/medical/' . strtoupper($file_name) . '.mp3';
				} */
				
				// if still not found, try collegiate source
				if(curl_get_file_size($url) == -1) {
					$url = 'http://mwd.s3.amazonaws.com/dictsounds/collegiate/' . substr($wav, 0, 1) . '/' . $file_name . '.mp3';
				}
			}
			else
			{
				$url = 'http://mwd.s3.amazonaws.com/dictsounds/collegiate/' . substr($wav, 0, 1) . '/' . str_replace('.wav', '.mp3', $wav);
			}

			// final check
			// return emtpy string if not found
			// speaker button if found
			if(curl_get_file_size($url) == -1)
				return '';
			else
				if($url_only)
					return $url;
				else
					return '<div class="icon-speaker play-sound-button" data-src-wav="' . $url . '"></div>';
		}
		else
		{
			return '';
		}
	}

	/*
	 * format functional label
	 */
	public static function _fl($str)
	{
		$str = json_decode($str);

		switch($str[0])
		{
			case 'n':
				return 'noun';
			case 'vb':
				return 'verb';
			case 'adj':
				return 'adjective';
		}

		return $str[0];
	}

	/*
	 * format definition tag
	 *
	 * @param string $str 		the string to format
	 * @param mixed $dictionary		string slug or id of dictionary
	 * @param boolean $a_new_tab	open a tag in new tab
	 *
	 * @return string
	 */
	public static function _def($str, $dictionary, $a_new_tab = false)
	{
		$json = json_decode($str);
		$str = is_null($json) ? array($str) : $json;

		$h_phrasev = $sn = ''; $html = ''; $xml_str = '';
		$xml = '';

		// if $dictionary is number, we change it to string slug.
		// TODO: create function for this ?
		if(is_numeric($dictionary)) {
			switch($dictionary)
			{
				case 1: $dictionary = 'elearner';
					break;
				case 2: $dictionary = 'collegiate';
					break;
				case 3: $dictionary = 'medical';
					break;
				case 4: $dictionary = 'intermediate';
					break;
				case 5: $dictionary = 'elementary';
					break;
			}
		}

		$a_target = '';
		if($a_new_tab) {
			$a_target = ' target="_blank"';
		}

		if($dictionary == 'medical')
		{
			foreach($str as $key => $item)
			{
				$xml .= '<sn>' . ($key + 1) . '</sn><dt>' . $item . '</dt>';
			}
		}
		else
		{
			$xml = $str[0];
		}

		$dom = new DOMDocument;
		@$dom->loadXML('<def>' . $xml . '</def>');
		
		if($dictionary == 'collegiate') {
			foreach($dom->documentElement->childNodes as $node) {
				if($node->nodeName == 'vt') {
					$xml_str .= $node->ownerDocument->saveXML($node);
				}
				if($node->nodeName == 'sensb') {
					$sense_tags = $node->getElementsByTagName('sense');
					foreach($sense_tags as $sense_tag) {
						$xml_str .= get_inner_html($sense_tag);
					}
				}
			}
			@$dom->loadXML('<def>' . $xml_str . '</def>');
		}

		foreach($dom->documentElement->childNodes as $node_key => $node)
		{
			if($node->nodeName == 'gram') {
				$h_phrasev .= '<span class="gram">[<em>' . $node->nodeValue . '</em>]</span>';
				$h_phrasev = WFormatter::_hw($h_phrasev);
				$prev_tag = 'gram';
			}
			if($node->nodeName == 'phrasev') {
				$nodeValue = get_inner_html($node);
				$nodeValue = str_replace('<pva>', ' <strong class="pva">', $nodeValue);
				$nodeValue = str_replace('</pva>', '</strong>', $nodeValue);
				$nodeValue = str_replace('<pvl>', ' <em>', $nodeValue);
				$nodeValue = str_replace('</pvl>', '</em>', $nodeValue);
				$nodeValue = $nodeValue . ' ';

				if($prev_tag != 'sn') {
					$h_phrasev .= $nodeValue;
				}
				else {
					$sn .= $nodeValue;
				}
				$prev_tag = 'phrasev';
			}
			if($node->nodeName == 'ssl') {
				$nodeValue = ' <em class="ssl">' . $node->nodeValue . '</em>';

				if(($prev_tag == 'phrasev' || $prev_tag == 'ssl') && $dom->documentElement->childNodes->item($key - 2)->nodeName != 'sn') {
					$h_phrasev .= $nodeValue;
				}
				else {
					$sn .= $nodeValue;
				}
				$prev_tag = 'ssl';
			}
			if($node->nodeName == 'sl') {
				$h_phrasev .= ' <em class="sl">' . $node->nodeValue . '</em>';
				$prev_tag = 'sl';
			}
			if($node->nodeName == 'vt') {
				$html .= '<div class="vt">' . $node->nodeValue . '</div>';
			}
			if($node->nodeName == 'sn') {
				$value = get_inner_html($node);
				$snp = $node->getElementsByTagName('snp')->item(0);
				if(!is_null($snp)) $node->removeChild($snp);
				$snp_class = $node->nodeValue == '' ? '<span class="sn-snp">' : '<span>';
				$value = str_replace('<snp>', $snp_class, $value);
				$value = str_replace('</snp>', '</span>', $value);
				
				$asn = explode(' ', $value);
				if(count($asn) == 1) {
					if(is_numeric(trim($value))) {
						$sn_class = 'msn';
					}
					else {
						$sn_class = 'sn';
						$value = '<span class="ssn">' . $value . '</span>';
					}
					$sn .= '<span class="definition-' . $sn_class . '">' . $value . '</span> ';
				}
				else {
					if(is_numeric(trim($asn[0]))) {					
						$sn .= '<span class="definition-msn">' . $asn[0] . '</span> ';
						unset($asn[0]);
						$ssn = '';
						if(strlen($asn[1]) == 1) {
							$ssn = '<span class="ssn">' . $asn[1] . '</span>';
							unset($asn[1]);
						}
						$sn .= '<span class="definition-sn">' . $ssn . implode('', $asn) . '</span> ';
					}
					else {
						$ssn = '';
						if(strlen($asn[0]) == 1) {
							$ssn = '<span class="ssn">' . $asn[0] . '</span>';
							unset($asn[0]);
						}
						$sn .= '<span class="definition-sn">' . $ssn . implode(' ', $asn) . '</span> ';
					}
				}
				$prev_tag = 'sn';
			}
			if($node->nodeName == 'sgram') {
				$sn .= ' <span class="sgram">[<i>' . $node->nodeValue . '</i>]</span> ';
			}
			if($node->nodeName == 'slb') {
				$sn .= '<em class="slb">' . $node->nodeValue . '</em>';
			}
			if($node->nodeName == 'ssl' && $prev_sn) {
				$sn .= ' <em class="ssl">' . $node->nodeValue . '</em>';
			}
			if($node->nodeName == 'sd') {
				$sn .= ' <em class="sd">' . $node->nodeValue . '</em>';				
			}
			if($node->nodeName == 'dt') {
				$line = str_replace('{bc}', '<b>:</b> ', get_inner_html($node));
				$line = str_replace('{bs}', '', $line);
				$line = str_replace('<vi>', '<span class="example-group"><span class="example">', $line);
				$line = str_replace('</vi>', '</span></span>', $line);
				$line = str_replace('<un>', '<span class="un-group"><b>&mdash;</b> ', $line);
				$line = str_replace('</un>', '</span>', $line);
				$line = str_replace('<snote>', '<span class="snote-group"><b>&mdash;</b> ', $line);
				$line = str_replace('</snote>', '</span>', $line);				
				$line = str_replace('<phrase>','<strong>', $line);
				$line = str_replace('</phrase>','</strong> ', $line);
				$line = str_replace('<wsgram>','<span class="wsgram">[<em>', $line);
				$line = str_replace('</wsgram>','</em>]</span>', $line);
				$line = WFormatter::specialchars_to_html($line);

				//<dx>see also <dxt>tell on <dxn>1 (below)</dxn></dxt></dx>
				preg_match_all('/<dx>(.*?)<\/dx>/', $line, $dx_tags);
				$dom2 = new DOMDocument;
				foreach($dx_tags[0] as $v) {
					$url = array();
					@$dom2->loadXML($v);
					$dxts = $dom2->getElementsByTagName('dxt');
					for($i = 0; $i < $dxts->length; $i++)
					{
						$dxn = $dxts->item($i)->getElementsByTagName('dxn');
						$dxn_v = '';
						if($dxn->length > 0) {
							$dxn_v = $dxn->item(0)->nodeValue;
							$dxts->item($i)->removeChild($dxn->item(0));
						}
						$entry = trim($dxts->item($i)->nodeValue);
						if(preg_match('/{h,(.*?)}/', $entry, $sup)) {
							$sub_tag = '<sup>' . $sup[1] . '</sup>';
						}
						$entry = preg_replace('/\{.*?\}/', '', $entry);
						$url[] = $sub_tag . '<a href="' . locale_home_url() . '/?r=dictionary/' . $dictionary . '/' . $entry . '"' . $a_target . '>' . strtoupper($entry) . '</a> ' . $dxn_v;
					}

					$line = str_replace($v, '<div class="dx">see also ' . implode(', ', $url) . '</div>', $line);
				}

				preg_match_all('/<sx>(.*?)<\/sx>/', $line, $sx);
				foreach($sx[1] as $key => $item) {
					$comma = count($sx[1]) > 1 && count($sx[1]) > $key + 1 ? ', ' : '';
					$iem_text = preg_replace('/\{.*?\}|<sxn>.*?<\/sxn>/', '', $item);
					$line = str_replace('<sx>' . $item . '</sx>', ' <a href="' . locale_home_url() . '/?r=dictionary/' . $dictionary . '/' . $iem_text . '"' . $a_target . '>' . strtoupper($iem_text) . '</a>' . $comma, $line);
				}

				// ugly hack
				// check if next node is usage, if not, end line
				if($dom->documentElement->childNodes->item($node_key + 1)->nodeName != 'usage') {
					// end a line
					$line = '<div class="definition-content">' . $line . '</div>';
					if($h_phrasev != '') {
						$html .= '<div class="h_phrasev">' . $h_phrasev . '</div>';
					}

					if($sn != '') {
						$sn = '<div class="definition-head">' . $sn . '</div>';
					}
					$html .= '<div class="definition-sense">' . $sn . $line . '</div>';
					$h_phrasev = $sn = '';
				}
			}
			if($node->nodeName == 'usage') {
				$line .= '<div><strong>Usage</strong> ' . get_inner_html($node) . '</div>';

				// end a line
				$line = '<div class="definition-content">' . $line . '</div>';
				if($h_phrasev != '') {
					$html .= '<div class="h_phrasev">' . $h_phrasev . '</div>';
				}

				if($sn != '') {
					$sn = '<div class="definition-head">' . $sn . '</div>';
				}
				$html .= '<div class="definition-sense">' . $sn . $line . '</div>';
				$h_phrasev = $sn = '';
			}
		}

		$html = WFormatter::replace_tags('it', 'em', $html);

		return $html == '' ? '<div></div>' : $html;		
	}

	/*
	 * format definition for Thesaurus dictionary only
	 */
	public static function _thesaurus_def($str)
	{
		$dom = new DOMDocument;
		@$dom->loadXML($str);

		//$xPath = new DOMXPath($dom);
		//$nodes = $xPath->query('//def/sensb/sense');

		$html = $sn = $line = '';$start_sn = false;
		foreach($dom->documentElement->childNodes as $key => $node)
		{
			if($node->nodeName == 'sn') {
				if($start_sn) {
					// end a line
					$line = '<div class="definition-content">' . $line . '</div>';

					if($sn != '') {
						$sn = '<div class="definition-head">' . $sn . '</div>';
					}
					$html .= '<div class="definition-sense">' . $sn . $line . '</div>';

					$sn = $line = '';
				}

				$sn .= '<span class="definition-msn">' . $node->nodeValue . '</span> ';
				$prev_tag = 'sn';
				$start_sn = true;
			}
			
			if($node->nodeName == 'dt') {
				$line = str_replace('{bc}', '<b>:</b> ', get_inner_html($node) . $line);
				$line = str_replace('<vi>', '<span class="example-group"><span class="example">', $line);
				$line = str_replace('</vi>', '</span></span>', $line);
				$line = str_replace('<un>', '<span class="un-group"><b>&mdash;</b> ', $line);
				$line = str_replace('</un>', '</span>', $line);
				$line = str_replace('<snote>', '<span class="snote-group"><b>&mdash;</b> ', $line);
				$line = str_replace('</snote>', '</span>', $line);
				$line = str_replace('<it>','<em>', $line);
				$line = str_replace('</it>','</em>', $line);
			}
			
			if($node->nodeName == 'syn') {
				$nodeValue = get_inner_html($node);

				preg_match('/<sc>(.*?)<\/sc>/', $nodeValue, $sc);
				$nodeValue = str_replace('<sc>', '<a class="syn-sc" href="' . locale_home_url() . '/?r=dictionary/collegiate/' . $sc[1] . '">', $nodeValue);
				$nodeValue = str_replace('</sc>', '</a>', $nodeValue);

				$line .= '<div><strong>Synonyms</strong> ' . $nodeValue . '</div>';
			}
			if($node->nodeName == 'rel') {
				$line .= '<div><strong>Related Words</strong> ' . $node->nodeValue . '</div>';
			}
			if($node->nodeName == 'con') {
				$line .= '<div><strong>Contrasted Words</strong> ' . $node->nodeValue . '</div>';
			}
			if($node->nodeName == 'ant') {
				$line .= '<div><strong>Antonyms</strong> ' . $node->nodeValue . '</div>';
			}
			if($node->nodeName == 'id') {
				$line .= '<div><strong>Idioms</strong> ' . $node->nodeValue . '</div>';
			}
			if($key + 1 == $dom->documentElement->childNodes->length) {
				// end a line
				$line = '<div class="definition-content">' . $line . '</div>';

				if($sn != '') {
					$sn = '<div class="definition-head">' . $sn . '</div>';
				}
				$html .= '<div class="definition-sense">' . $sn . $line . '</div>';
				
				$sn = '';
			}
		}

		return WFormatter::specialchars_to_html($html);
		
	}

	/*
	 * get Image
	 * return false if not found
	 */
	public static function _art($str, $entry, $dictionary)
	{
		$str = json_decode($str);

		// temporary fix for elementary dictionary
		if($dictionary == 'elementary') {
			$filename = rawurlencode($entry . '.jpg');
			$caption = $entry;

			$img_url = 'http://mwd.s3.amazonaws.com/dictimages/elementary/' . $filename;

			if(curl_get_file_size($img_url) == -1)
				return '';

			$image = '<img src="' . $img_url . '" alt="' . $entry . '">';

			$caption = '<p>' . $caption . '</p>';

			return '<div class="definition-image">' . $image . $caption . '</div>';
		}

		if(is_null($str)) {
			return '';
		}

		$xml = '';
		foreach($str as $item)
		{
			$xml .= $item;
		}

		$dom = new DOMDocument;
		@$dom->loadXML('<art>' . $xml . '</art>');

		$bmp = $dom->getElementsByTagName('bmp');
		if($bmp->length > 0) {
			$file = explode('.', $bmp->item(0)->nodeValue);
		} else {
			$artref = $dom->getElementsByTagName('artref');
			$file = explode('.', $artref->item(0)->getAttribute('id'));
		}

		$cap = $dom->getElementsByTagName('cap');
		if($cap->length > 0) {
			$caption = get_inner_html($cap->item(0));
		} else {
			$capt = $dom->getElementsByTagName('capt');
			$caption = get_inner_html($capt->item(0));
		}

		switch($dictionary)
		{
			case 'elearner': $dir = 'learners/';
				break;
			case 'collegiate' : $dir = 'collegiate/';
				break;
			case 'medical': $dir = 'medical/';
				break;
			case 'intermediate': $dir = 'intermediate/';
				break;
			case 'elementary': $dir = 'elementary/';
				break;
		}

		$filename = $file[0] . '.gif';

		$image = '<img src="http://mwd.s3.amazonaws.com/dictimages/' . $dir . $filename . '" alt="' . $entry . '">';

		$caption = str_replace('<it>','<em>', $caption);
		$caption = str_replace('</it>','</em>', $caption);
		$caption = '<p>' . $caption . '</p>';

		$html = '<div class="definition-image">' . $image . $caption . '</div>';

		return $html;
	}

	/*
	 * get synonyms
	 */
	public static function _synonyms($word, $dictionary)
	{
		$synonyms = '';
		$dom = new DOMDocument;

		if($dictionary == 'collegiate')
		{
			$str = json_decode($word->definition);
			@$dom->loadXML('<def>' . $str[0] . '</def>');

			$ss = $dom->getElementsByTagName('ss');
			$a = array();
			for($i = 0; $i < $ss->length; $i++)
			{
				$a[] = $ss->item($i)->nodeValue;
			}
		}
		else 
		{
			$a = json_decode($word->synonyms_see_ref);
		}

		if(!is_null($a)) {
			foreach($a as $key => $text)
			{
				$a[$key] = '<a href="' . locale_home_url() . '/?r=dictionary/' . $dictionary . '/' . $text . '"><u>' . strtoupper($text) . '</u></a>'; 
			}

			if(!empty($a)) {
				$synonyms = '<div>see ' . implode(', ', $a) . '</div>';
			}
		}

		// uro tags
		$uro_tags = (array) json_decode($word->undefined_run_on);
		$uro = '';$ure = '';$utxt = ''; $start_ure = false;

		foreach($uro_tags as $v)
		{
			$uro_dom = new DOMDocument;
			$uro_dom->loadXML('<uro>' . $v . '</uro>');

			foreach($uro_dom->documentElement->childNodes as $node_key => $node)
			{
				if($node->nodeName == 'ure') {
					// end line
					/* if($start_ure) {
						$uro .= '<div class="uro"> &mdash; ' . $ure . ' ' . $fl . $utxt . '</div>';
						$ure = '';
					} */

					$ure .= '<strong class="ure">' . WFormatter::_hw($node->nodeValue) . '</strong>';
					$start_ure = true;
				}
				if($node->nodeName == 'vr') {
					$obj = new stdClass;
					$obj->variant = get_inner_html($node);
					$ure .= WFormatter::_variant($obj, $dictionary);
				}
				if($node->nodeName == 'fl') {
					$fl = $node->nodeValue;
				}
				if($node->nodeName == 'utxt') {
					$utxt = str_replace('<vi>', '<span class="example-group"><span class="example">', get_inner_html($node));
					$utxt = str_replace('</vi>', '</span></span>', $utxt);
					$utxt = str_replace('<un>', '<span class="un-group"><b>&mdash;</b> ', $utxt);
					$utxt = str_replace('</un>', '</span>', $utxt);
					$utxt = str_replace('<snote>', '<span class="snote-group"><b>&mdash;</b> ', $utxt);
					$utxt = str_replace('</snote>', '</span>', $utxt);				
					$utxt = str_replace('<phrase>','<strong>', $utxt);
					$utxt = str_replace('</phrase>','</strong> ', $utxt);
					$utxt = str_replace('<wsgram>','<span class="wsgram">[<em>', $utxt);
					$utxt = str_replace('</wsgram>','</em>]</span>', $utxt);
				}

				if($node_key + 1 == $uro_dom->documentElement->childNodes->length) {
					// end a line
					if($start_ure) {
						$uro .= '<div class="uro"> &mdash; ' . $ure . ' ' . $fl . $utxt . '</div>';
						$ure = '';
					}
				}
			}
		}

		// dro tags
		$dro_tags = (array) json_decode($word->defined_run_on);
		$dro = '';

		foreach($dro_tags as $v)
		{
			$phrase = $dx = $vr = $gram = $def = '';
			$dom->loadXML('<dro>' . $v . '</dro>');

			foreach($dom->documentElement->childNodes as $node)
			{
				if($node->nodeName == 'drp') {
					$phrase = $node->nodeValue;
				}
				if($node->nodeName == 'dre') {
					$phrase = $node->nodeValue;
				}
				if($node->nodeName == 'gram') {
					$gram = ' [<em>' . $node->nodeValue . '</em>]';
				}
				if($node->nodeName == 'dx') {
					$dx = WFormatter::_dx(get_inner_html($node), $dictionary);
				}
				if($node->nodeName == 'vr') {
					$vr = get_inner_html($node);
					$vr = WFormatter::replace_tags('vl', 'em', $vr);
					$vr = str_replace('<va>', ' <strong class="ure">', $vr);
					$vr = str_replace('</va>', '</strong>', $vr);
				}
				if($node->nodeName == 'def') {
					$def = get_inner_html($node);
				}
			}

			$dro .= '<div class="uro"> &mdash; <strong class="ure">' . $phrase . '</strong>' . $gram . $vr . $dx . '</div>' . WFormatter::_def($def, $dictionary);
		}

		$html = $synonyms . $uro . $dro;

		if($html == '') {
			return false;
		}

		$html = WFormatter::replace_tags('it', 'em', $html);

		return '<div class="definition-syn">' . $html . '</div>';
	}

	/*
	 * get dx tags
	 * used for <dx> tag inside <def>
	 */
	public static function _dx($str, $dictionary)
	{
		preg_match('/<dxt>(.*?)<\/dxt>/', $str, $dxt);
		
		$sub_tag = '';
		if(preg_match('/{h,(.*?)}/', $str, $sup)) {
			$sub_tag = '<sup>' . $sup[1] . '</sup>';
		}

		$entry = preg_replace('/\{.*?\}/', '', $dxt[1]);
		$a = $sub_tag . '<a href="' . locale_home_url() . '/?r=dictionary/' . $dictionary . '/' . $entry . '"><u>' . strtoupper($entry) . '</u></a>';

		return preg_replace('/<dxt>(.*?)<\/dxt>/', $a, '; ' . $str);
	}

	/*
	 * format first level <dx> tag
	 */
	public static function _dir_cross_ref($str, $dictionary)
	{
		$str = json_decode($str);

		if(empty($str)) {
			return '';
		}

		preg_match('/<dxt>(.*?)<\/dxt>/', $str[0], $dxt);
		
		$sub_tag = '';
		if(preg_match('/{h,(.*?)}/', $str[0], $sup)) {
			$sub_tag = '<sup>' . $sup[1] . '</sup>';
		}

		$entry = preg_replace('/\{.*?\}/', '', $dxt[1]);
		$entry = trim(preg_replace('/<dxn>(.*?)<\/dxn>/', '', $entry));
		$a = $sub_tag . '<a href="' . locale_home_url() . '/?r=dictionary/' . $dictionary . '/' . $entry . '"><u>' . strtoupper($entry) . '</u></a>';

		preg_match('/<dxn>(.*?)<\/dxn>/', $str[0], $dxn);
		if(!empty($dxn)) {
			$a .= ' ' . $dxn[1];
		}

		$html = preg_replace('/<dxt>(.*?)<\/dxt>/', $a, $str[0]);
		$html = WFormatter::replace_tags('it', 'em', $html);

		return '&mdash; ' . $html;
	}

	/*
	 * get Etymology
	 * return false if not found
	 */
	public static function _etymology($str)
	{
		$str = json_decode($str);

		if(is_null($str) || $str == 'null' || empty($str)) {
			return false;
		}

		$output = WFormatter::replace_tags('it', 'em', $str[0]);

		preg_match_all('/{h,(.*?)}/', $output, $superscripts);

		foreach($superscripts[0] as $key => $sup) {
			$output = str_replace($sup, '<sup>' . $superscripts[1][$key] . '</sup>', $output);
		}

		return WFormatter::specialchars_to_html($output);
	}

	/*
	 * get date field
	 * return false if not found
	 */
	public static function _date($str)
	{
		$str = json_decode($str);
		preg_match('/<date>(.*?)<\/date>/', $str[0], $date);
		
		return isset($date[1]) ? $date[1] : false;
	}

	/*
	 * this function return HTML code of given sgml
	 */
	public static function _pr($str)
	{
		if(is_object($str)) {
			$s[0] = $str->item(0)->nodeValue;
		}
		else {
			$s = json_decode($str);
			
			if(is_null($s)) {
				$s = array($str);
			}			
		}

		if($s[0] == 'null') {
			return false;
		}

		$text = WFormatter::specialchars_to_html($s[0]);
		$text = WFormatter::replace_tags('it', 'em', $text);

		return $text == '' ? false : ' <span class="unicode-pr">\\' . $text . '\\</span>';
	}
	
	/*
	 * replace special character code to html entities
	 */
	public static function specialchars_to_html($str)
	{
		$pr = array(
				'{Aacute}' => '&#x00C1;',
				'{aacute}' => '&#x00E1;',
				'{Abreve}' => '&#x0102;',
				'{abreve}' => '&#x0103;',
				'{acedil}' => '&#x0061;&#x0327;',
				'{Acirc}' => '&#x00C2;',
				'{acirc}' => '&#x00E2;',
				'{acrcudot}' => '&#x1EAD;',
				'{acute}' => '&#x00B4;',
				'{adot}' => '&#x0227;',
				'{aeligac}' => '&#x01FD;',
				'{aeligm}' => '&#x01E3;',
				'{AElig}' => '&#x00C6;',
				'{aelig}' => '&#x00E6;',
				'{Agrave}' => '&#x00C0;',
				'{agrave}' => '&#x00E0;',
				'{Agr}' => '&#x0391;',
				'{agr}' => '&#x03B1;',
				'{agrvgr}' => '&#x1F70;',
				'{ahacek}' => '&#x01CE;',
				'{ahookac}' => '&#x0105;&#x0301;',
				'{Ahook}' => '&#x0104;',
				'{ahook}' => '&#x0105;',
				'{Amacr}' => '&#x0100;',
				'{amacr}' => '&#x0101;',
				'{amactil}' => '&#x0101;&#x0303;',
				'{amp}' => '&#x0026;',
				'{aposapos}' => '&#x02BE;&#x0339;',
				'{apos}' => '&#x02BC;',
				'{arch}' => '&#x2322;',
				'{Aring}' => '&#x00C5;',
				'{aring}' => '&#x00E5;',
				'{ast}' => '&#x002A;',
				'{Atilde}' => '&#x00C3;',
				'{atilde}' => '&#x00E3;',
				'{Audot}' => '&#x1EA0;',
				'{auline}' => '&#x0061;&#x0331;',
				'{Auml}' => '&#x00C4;',
				'{auml}' => '&#x00E4;',				
				'{ayn}' => '&#x02BF;',
				'{bbar}' => '&#x0180;',
				'{bcirc}' => '&#x0062;&#x0302;',
				'{Bgr}' => '&#x0392;',
				'{bgr}' => '&#x03B2;',
				'{breve}' => '&#x02D8;',
				'{bsol}' => '&#x005C;',
				'{buline}' => '&#x1E07;',
				'{bull}' => '&#x2022;',
				'{Cacute}' => '&#x0106;',
				'{cacute}' => '&#x0107;',
				'{capital}' => '&#x273D;',
				'{capos}' => '&#x0053;&#x0313;',
				'{cap}' => '&#x2229;',
				'{Ccedil}' => '&#x00C7;',
				'{ccedil}' => '&#x00E7;',
				'{cedil}' => '&#x00B8;',
				'{cent}' => '&#x00A2;',
				'{Chacek}' => '&#x010C;',
				'{chacek}' => '&#x010D;',
				'{check}' => '&#x2713;',
				'{chempt}' => '&#x00B7;',
				'{circ}' => '&#x02C6;',
				'{colon}' => '&#x003A;',
				'{commat}' => '&#x0040;',
				'{comma}' => '&#x002C;',
				'{copy}' => '&#x00A9;',
				'{Cudot}' => '&#x0043;&#x0323;',
				'{cudot}' => '&#x0063;&#x0323;',
				'{cup}' => '&#x222A;',
				'{curoot2}' => '&#x221B;&#x035E;',
				'{curoot}' => '&#x221B;',
				'{dagger}' => '&#x2020;',
				'{Dagger}' => '&#x2021;',
				'{Dbar}' => '&#x0110;',
				'{dbar}' => '&#x0111;',
				'{dblbond}' => '&#x003D;',
				'{dblhyph}' => '&#x1D113;',
				'{Dcedil}' => '&#x1E10;',
				'{dcedil}' => '&#x1E11;',
				'{deg}' => '&#x00B0;',
				'{Dgr}' => '&#x0394;',
				'{dgr}' => '&#x03B4;',
				'{dhacek}' => '&#x0064;&#x030C;',
				'{divide}' => '&#x00F7;',
				'{dollar}' => '&#x0024;',
				'{dollar2}' => '&#x0024;',
				'{Dudot}' => '&#x1E0C;',
				'{dudot}' => '&#x1E0D;',
				'{eacgr}' => '&#x1F73;',
				'{Eacute}' => '&#x00C9;',
				'{eacute}' => '&#x00E9;',
				'{eaposgr}' => '&#x1F10;',
				'{Ebreve}' => '&#x0114;',
				'{ebreve}' => '&#x0115;',
				'{ecedil}' => '&#x0229;',
				'{ecircac}' => '&#x1EBF;',
				'{Ecirc}' => '&#x00CA;',
				'{ecirc}' => '&#x00EA;',
				'{ecrcudot}' => '&#x1EC7;',
				'{edot}' => '&#x0117;',
				'{EEgr}' => '&#x0397;',
				'{eegr}' => '&#x03B7;',
				'{Egrave}' => '&#x00C8;',
				'{egrave}' => '&#x00E8;',
				'{Egr}' => '&#x0395;',
				'{egr}' => '&#x03B5;',
				'{Ehacek}' => '&#x011A;',
				'{ehacek}' => '&#x011B;',
				'{Ehook}' => '&#x0118;',
				'{ehook}' => '&#x0119;',
				'{emacrac}' => '&#x1E17;',
				'{Emacr}' => '&#x0112;',
				'{emacr}' => '&#x0113;',
				'{emsp}' => '&#x2003;',
				'{eng}' => '&#x014B;',
				'{ensp}' => '&#x2002;',
				'{equals}' => '&#x003D;',
				'{eth}' => '&#x00F0;',
				'{etilde}' => '&#x1EBD;',
				'{Euml}' => '&#x00CB;',
				'{euml}' => '&#x00EB;',
				'{excl}' => '&#x0021;',
				'{fermata}' => '&#x1D110;',
				'{flat}' => '&#x266D;',
				'{Forte}' => '&#xFB00;',
				'{fuml}' => '&#x0066;&#x0308;',
				'{gbar}' => '&#x01E5;',
				'{Gbreve}' => '&#x011E;',
				'{gbreve}' => '&#x011F;',
				'{Gcirc}' => '&#x011C;',
				'{gdot}' => '&#x0121;',
				'{Ggr}' => '&#x0393;',
				'{ggr}' => '&#x03B3;',
				'{Ghacek}' => '&#x01E6;',
				'{ghacek}' => '&#x01E7;',
				'{glotstop}' => '&#x0294;',
				'{gmacr}' => '&#x1E21;',
				'{grave}' => '&#x0060;',
				'{gt}' => '&#x003E;',
				'{hacek}' => '&#x02C7;',
				'{hairsp}' => '&#x200A;',
				'{hamzah}' => '&#x02BE;',
				'{Hcedil}' => '&#x1E28;',
				'{hcedil}' => '&#x1E29;',
				'{hellip}' => '&#x2026;',
				'{hhook}' => '&#x0068;&#x0328;',
				'{hstres}' => '&#x02C8;',
				'{hubreve}' => '&#x1E2B;',
				'{Hudot}' => '&#x1E24;',
				'{hudot}' => '&#x1E25;',
				'{huhacek}' => '&#x0068;&#x032C;',
				'{Huline}' => '&#x0048;&#x0331;',
				'{huline}' => '&#x1E96;',
				'{Huring}' => '&#x0048;&#x0325;',
				'{hyphen}' => '&#x002D;',
				'{Iacute}' => '&#x00CD;',
				'{iacute}' => '&#x00ED;',
				'{iumlac}' => '&#x1E2F;',
				'{ibar}' => '&#x0268;',
				'{ibreve}' => '&#x012D;',
				'{icircgr}' => '&#x03B9;&#x0302;',
				'{Icirc}' => '&#x00CE;',
				'{icirc}' => '&#x00EE;',
				'{idblac}' => '&#x0069;&#x030B;',
				'{Idot}' => '&#x0130;',
				'{iexcl}' => '&#x00A1;',
				'{Igrave}' => '&#x00CC;',
				'{igrave}' => '&#x00EC;',
				'{Igr}' => '&#x0399;',
				'{igr}' => '&#x03B9;',
				'{ihacek}' => '&#x01D0;',
				'{Imacr}' => '&#x012A;',
				'{imacr}' => '&#x012B;',
				'{isc}' => '&#x012B;',
				'{imactil}' => '&#x012B;&#x0303;',
				'{index}' => '&#x261E;',
				'{infin}' => '&#x221E;',
				'{int}' => '&#x222B;',
				'{inodot}' => '&#x0131;',
				'{ipa006}' => '&#x0250;',
				'{ipa007}' => '&#x0251;',
				'{ipa009}' => '&#x0252;',
				'{ipa012}' => '&#x00E6;',
				'{ipa018}' => '&#x028C;',
				'{ipa025}' => '&#x03B2;',
				'{ipa030}' => '&#x00E7;',
				'{ipa039}' => '&#x02A4;',
				'{ipa040a}' => '&#x00F0;',
				'{ipa044i}' => '&#x0259;',
				'{ipa044}' => '&#x0259;',
				'{ipa046}' => '&#x025A;',
				'{ipa050}' => '&#x025B;',
				'{ipa054}' => '&#x025D;',
				'{ipa058}' => '&#x0261;',
				'{ipa062}' => '&#x0263;',
				'{ipa064b}' => '&#x02B0;',
				'{ipa066}' => '&#x0127;',
				'{ipa071}' => '&#x0265;',
				'{ipa075}' => '&#x0268;',
				'{ipa076}' => '&#x0269;',
				'{ipa077a}' => '&#x026A;',
				'{ipa078a}' => '&#x026A;&#x0335;',
				'{ipa080}' => '&#x02B2;',
				'{ipa087as}' => '&#x006C;&#x0329;',
				'{ipa087b}' => '&#x026B;',
				'{ipa089}' => '&#x026C;',
				'{ipa095s}' => '&#x006D;&#x0329;',
				'{ipa097}' => '&#x026F;',
				'{ipa100as}' => '&#x006E;&#x0329;',
				'{ipa103}' => '&#x0272;',
				'{ipa104as}' => '&#x014B;&#x0329;',
				'{ipa104a}' => '&#x014B;',
				'{ipa114}' => '&#x00F8;',
				'{ipa115}' => '&#x0153;',
				'{ipa117}' => '&#x0254;',
				'{ipa132}' => '&#x027E;',
				'{ipa135}' => '&#x0279;',
				'{ipa138a}' => '&#x0280;',
				'{ipa143a}' => '&#x0283;',
				'{ipa149}' => '&#x02A7;',
				'{ipa151}' => '&#x03B8;',
				'{ipa156a}' => '&#x028A;',
				'{ipa167}' => '&#x03C7;',
				'{ipa172}' => '&#x028F;',
				'{ipa178}' => '&#x0292;',
				'{ipa185}' => '&#x0294;',
				'{ipa189}' => '&#x0295;',
				'{ipa215b}' => '&#x02D0;',
				'{iquest}' => '&#x00BF;',
				'{irrat}' => '&#x221A;&#x2012;&#x0305;1&#x0305;',
				'{epsilon}' => '&#949;',
				'{iumlac}' => '&#x1E2F;',
				'{Iuml}' => '&#x00CF;',
				'{iuml}' => '&#x00EF;',
				'{jhacek}' => '&#x006A;&#x030C;',
				'{kapos}' => '&#x006B;&#x0313;',
				'{Kgr}' => '&#x039A;',
				'{kgr}' => '&#x03BA;',
				'{KHgr}' => '&#x03A7;',
				'{khgr}' => '&#x03C7;',
				'{kudot}' => '&#x1E33;',
				'{kuline}' => '&#x1E35;',
				'{lang}' => '&#x3008;',
				'{laquo}' => '&#x00AB;',
				'{lbargr}' => '&#x019B;',
				'{lcub}' => '&#x007B;',
				'{ldquo}' => '&#x201C;',
				'{le}' => '&#x2264;',
				'{Lgr}' => '&#x039B;',
				'{lgr}' => '&#x03BB;',
				'{lpar}' => '&#x0028;',
				'{lsqb}' => '&#x005B;',
				'{lsquo}' => '&#x2018;',
				'{lstres}' => '&#x02CC;',
				'{Lstrok}' => '&#x0141;',
				'{lstrok}' => '&#x0142;',
				'{lt}' => '&#x003C;',
				'{Ludot}' => '&#x1E36;',
				'{ludot}' => '&#x1E37;',
				'{luline}' => '&#x1E3B;',
				'{macr}' => '&#x02C9;',
				'{malt}' => '&#x2720;',
				'{mdash}' => '&#x2014;',
				'{mdot}' => '&#x1E41;',
				'{Mgr}' => '&#x039C;',
				'{mgr}' => '&#x03BC;',
				'{middot}' => '&#x00B7;',
				'{minus}' => '&#x2212;',
				'{mudot}' => '&#x1E43;',
				'{muline}' => '&#x006D;&#x0331;',
				'{Nacute}' => '&#x0143;',
				'{nacute}' => '&#x0144;',
				'{natur}' => '&#x266E;',
				'{nbsp}' => '&#x00A0;',
				'{ndash}' => '&#x2013;',
				'{Ndot}' => '&#x1E44;',
				'{ndot}' => '&#x1E45;',
				'{ne}' => '&#x2260;',
				'{Ngr}' => '&#x039D;',
				'{ngr}' => '&#x03BD;',
				'{Nhacek}' => '&#x0147;',
				'{nhacek}' => '&#x0148;',
				'{Ntilde}' => '&#x00D1;',
				'{ntilde}' => '&#x00F1;',
				'{Nudot}' => '&#x1E46;',
				'{nudot}' => '&#x1E47;',
				'{nuline}' => '&#x1E49;',
				'{Numl}' => '&#x004E;&#x0308;',
				'{numl}' => '&#x006e;&#x0308;',
				'{numsp}' => '&#x2007;',
				'{num}' => '&#x0023;',
				'{Oacute}' => '&#x00D3;',
				'{oacute}' => '&#x00F3;',
				'{Obreve}' => '&#x014E;',
				'{obreve}' => '&#x014F;',
				'{ocircgrv}' => '&#x1ED3;',
				'{Ocirc}' => '&#x00D4;',
				'{ocirc}' => '&#x00F4;',
				'{ocrcudot}' => '&#x1ED9;',
				'{Odblac}' => '&#x0150;',
				'{odblac}' => '&#x0151;',
				'{odot}' => '&#x022F;',
				//'{odot}' => 'ȯ',
				'{oeligm}' => '&#x0153;&#x0305;',
				'{OElig}' => '&#x0152;',
				'{oelig}' => '&#x0153;',
				'{Ograve}' => '&#x00D2;',
				'{ograve}' => '&#x00F2;',
				'{Ogr}' => '&#x039F;',
				'{ogr}' => '&#x03BF;',
				'{ohacek}' => '&#x01D2;',
				'{OHgr}' => '&#x03A9;',
				'{ohgr}' => '&#x03C9;',
				'{ohookac}' => '&#x00F3;&#x0328;',
				'{Ohook}' => '&#x01EA;',
				'{ohook}' => '&#x01EB;',
				'{omacrac}' => '&#x1E53;',
				'{Omacr}' => '&#x014C;',
				'{omacr}' => '&#x014D;',
				'{openoac}' => '&#x0254;&#x0301;',
				'{openo}' => '&#x0254;',
				'{Oslash}' => '&#x00D8;',
				'{oslash}' => '&#x00F8;',
				'{Otilde}' => '&#x00D5;',
				'{otilde}' => '&#x00F5;',
				'{Ouml}' => '&#x00D6;',
				'{ouml}' => '&#x00F6;',
				'{papos}' => '&#x0070;&#x0313;',
				'{para}' => '&#x00B6;',
				'{par}' => '&#x2225;',
				'{peace}' => '&#x262E;',
				'{percnt}' => '&#x0025;',
				'{period}' => '&#x002E;',
				'{Pgr}' => '&#x03A0;',
				'{pgr}' => '&#x03C0;',
				'{PHgr}' => '&#x03A6;',
				'{phgr}' => '&#x03C6;',
				'{plusmn}' => '&#x00B1;',
				'{plus}' => '&#x002B;',
				'{point}' => '&#x2027;',
				'{pound}' => '&#x00A3;',
				'{prime}' => '&#x2032;',
				'{Prime}' => '&#x2033;',
				'{PSgr}' => '&#x03A8;',
				'{psgr}' => '&#x03C8;',
				'{qapos}' => '&#x0071;&#x0313;',
				'{qbang}' => '&#x203D;',
				'{quest}' => '&#x003F;',
				'{Racute}' => '&#x0154;',
				'{rang}' => '&#x3009;',
				'{raquo}' => '&#x00BB;',
				'{rarr}' => '&#x2192;',
				'{rcub}' => '&#x007D;',
				'{rdquo}' => '&#x201D;',
				'{Rgr}' => '&#x03A1;',
				'{rgr}' => '&#x03C1;',
				'{Rhacek}' => '&#x0158;',
				'{rhacek}' => '&#x0159;',
				'{rough}' => '&#x02BD;',
				'{rpar}' => '&#x0029;',
				'{rsqb}' => '&#x005D;',
				'{rsquo}' => '&#x2019;',
				'{rudia}' => '&#x0072;&#x0324;',
				'{Rudot}' => '&#x1E5A;',
				'{rudot}' => '&#x1E5B;',
				'{Ruline}' => '&#x1E5E;',
				'{ruline}' => '&#x1E5F;',
				'{ruml}' => '&#x0072;&#x0308',
				'{rx}' => '&#x211E;',
				'{Sacute}' => '&#x015A;',
				'{sacute}' => '&#x015B;',
				'{Sbreve}' => '&#x0053;&#x0306;',
				'{sbreve}' => '&#x0073;&#x0306;',
				'{Scedil}' => '&#x015E;',
				'{scedil}' => '&#x015F;',
				'{schwa}' => '&#x0259;',
				'{schwaac}' => '&#x0259;&#x0301;',
				'{schwadot}' => '&#x0259;&#x0307;',
				'{schwagrv}' => '&#x0259;&#x0300;',
				'{schwahk}' => '&#x0259;&#x0328;',
				'{sdash}' => '&#x2053;',
				'{sect}' => '&#x00A7;',
				'{semi}' => '&#x003B;',
				'{sfgr}' => '&#x03C2;',
				'{sglbond}' => '&#x2212;',
				'{Sgr}' => '&#x03A3;',
				'{sgr}' => '&#x03C3;',
				'{Shacek}' => '&#x0160;',
				'{shacek}' => '&#x0161;',
				'{sharp}' => '&#x266F;',
				'{shdot}' => '&#x00B7;',
				'{Shook}' => '&#x0053;&#x0328;',
				'{smacr}' => '&#x0073;&#x0304;',
				'{sol}' => '&#x002F;',
				'{smooth}' => '&#x02BC;',
				'{sroot}' => '&#x221A;',
				'{sroot2}' => '&#x221A;&#x035E;&#x0020;',
				'{Sudot}' => '&#x1E62;',
				'{sudot}' => '&#x1E63;',
				'{supn}' => '&#x207F;',
				'{supschwa}' => '&#x1D4A;',
				'{supw}' => '&#x02B7;',
				'{supy}' => '&#x02B8;',
				'{sup1}' => '&#x00B9;',
				'{sup2}' => '&#x00B2;',
				'{sup3}' => '&#x00B3;',
				'{szlig}' => '&#x00DF;',
				'{tearast}' => '&#x273D;',
				'{Tcedil}' => '&#x0162;',
				'{tcedil}' => '&#x0163;',
				'{tdot}' => '&#x1E6B;',
				'{Tgr}' => '&#x03A4;',
				'{tgr}' => '&#x03C4;',
				'{THgr}' => '&#x0398;',
				'{thgr}' => '&#x03B8;',
				'{thinsp}' => '&#x2009;',
				'{thorn}' => '&#x00FE;',
				'{thuline}' => '&#x0074;&#x035F;&#x0068;',
				'{tilde}' => '&#x02DC;',
				'{times}' => '&#x00D7;',
				'{trade}' => '&#x2122;',
				'{tribond}' => '&#x2261;',
				'{Tudot}' => '&#x1E6C;',
				'{tudot}' => '&#x1E6D;',
				'{tuhacek}' => '&#x0074;&#x032C;',
				'{tuline}' => '&#x1E6F;',
				'{Uacute}' => '&#x00DA;',
				'{uacute}' => '&#x00FA;',
				'{Ubreve}' => '&#x016C;',
				'{ubreve}' => '&#x016D;',
				'{Ucirc}' => '&#x00DB;',
				'{ucirc}' => '&#x00FB;',
				'{udblac}' => '&#x0171;',
				'{Udot}' => '&#x0055;&#x0307;',
				'{udot}' => '&#x0075;&#x0307;',
				'{uelig}' => '&#x1D6B;',
				'{ueligm}' => '&#x1D6B;&#x0305;',
				'{Ugrave}' => '&#x00D9;',
				'{ugrave}' => '&#x0075;&#x00F9;',
				'{Ugr}' => '&#x03A5;',
				'{ugr}' => '&#x03C5;',
				'{uhacek}' => '&#x01D4;',
				'{uhook}' => '&#x0173;',
				'{Umacr}' => '&#x016A;',
				'{umacr}' => '&#x016B;',
				'{umactil}' => '&#x016B;&#x0303;',
				'{uml}' => '&#x00A8;',
				'{Uring}' => '&#x016E;',
				'{uring}' => '&#x016F;',
				'{utilde}' => '&#x0169;',
				'{uudot}' => '&#x1EE5;',
				'{Uuml}' => '&#x00DC;',
				'{uuml}' => '&#x00FC;',
				'{uumlgrv}' => '&#x01DC;',
				'{wcirc}' => '&#x0175;',
				'{Xgr}' => '&#x039E;',
				'{xgr}' => '&#x03BE;',
				'{Yacute}' => '&#x00DD;',
				'{yacute}' => '&#x00FD;',
				'{yapos}' => '&#x0079;&#x0313;',
				'{ycirc}' => '&#x0177;',
				'{Ymacr}' => '&#x0059;&#x0232;',
				'{ymacr}' => '&#x0233;',
				'{yogh}' => '&#x0292;',
				'{Yuml}' => '&#x0178;',
				'{yuml}' => '&#x00FF;',
				'{Zacute}' => '&#x0179;',
				'{zacute}' => '&#x017A;',
				'{Zbar}' => '&#x01B5;',
				'{Zbreve}' => '&#x005A;&#x0306;',
				'{zbreve}' => '&#x007A;&#x0306;',
				'{Zcedil}' => '&#x005A;&#x0327;',
				'{zcedil}' => '&#x007A;&#x0327;',
				'{Zdot}' => '&#x017B;',
				'{zdot}' => '&#x017C;',
				'{zeronull}' => '&#x0030;&#x0338;',
				'{Zgr}' => '&#x0396;',
				'{zgr}' => '&#x03B6;',
				'{Zhacek}' => '&#x017D;',
				'{zhacek}' => '&#x017E;',
				'{zudia}' => '&#x007A;&#x0324;',
				'{Zudot}' => '&#x1E92;',
				'{zudot}' => '&#x1E93;',
				'{Zuline}' => '&#x1E94;',
				'{zuline}' => '&#x1E95;',

				// missing character code
				'{turneda}' => '&#x0250;',
				'{scripta}' => '&#x0251;',
				'{turnedscripta}' => '&#x0252;',
				'{invscripta}' => '&#x0252;',
				'{scriptainv}' => '&#x0252;',
				'{invv}' => '&#x028C;',
				'{beta}' => '&#x03B2;',
				'{dyogh}' => '&#x02A4;',
				'{ischwa}' => '&#x0259;',
				'{scriptg}' => '&#x0261;',
				'{gamma}' => '&#x0263;',
				'{suph}' => '&#x02B0;',
				'{hbar}' => '&#x0127;',
				'{turnedh}' => '&#x0265;',
				'{iota}' => '&#x0269;',
				'{sci}' => '&#x026A;',
				'{scibar}' => '&#x026A;&#x0335;',
				'{supj}' => '&#x02B2;',
				'{lsylab}' => '&#x006C;&#x0329;',
				'{lsyllab}' => '&#x006C;&#x0329;',
				'{ltilde}' => '&#x026B;',
				'{lbelted}' => '&#x026C;',
				'{msyllab}' => '&#x006D;&#x0329;',
				'{turnedm}' => '&#x026F;',
				'{nsyllab}' => '&#x006E;&#x0329;',
				'{nlefthook}' => '&#x0272;',
				'{engsyllab}' => '&#x014B;&#x0329;',
				'{fishhookr}' => '&#x027E;',
				'{turnedr}' => '&#x0279;',
				'{scr}' => '&#x0280;',
				'{esh}' => '&#x0283;',
				'{tesh}' => '&#x02A7;',
				'{theta}' => '&#x03B8;',				
				'{upsilon}' => '&#x028A;',
				'{chi}' => '&#x03C7;',
				'{scy}' => '&#x028F;',
				'{revglotstop}' => '&#x0295;',
				'{length}' => '&#x02D0;',
				'{mdash}' => '&mdash;'
			);

		return strtr($str, $pr);
		return html_entity_decode(strtr($str, $pr), ENT_COMPAT, 'ISO-8859-1');
	}

	/*
	 * search and replace both opening and closing tag
	 *
	 * @param mixed $old_tag		tag to search. Can be a string or an array of opening and closing tag. Tag name only
	 * @param mixed $new_tag		tag to replace. Can be a string or an array of opening and closing tag. Tag name only
	 * @param string $str			original string
	 *
	 * @return string
	 */
	public static function replace_tags($old_tags, $new_tags, $str)
	{
		if(!is_array($old_tags)) {
			$old_tags = array($old_tags, $old_tags);
		}

		if(!is_array($new_tags)) {
			$new_tags = array($new_tags, $new_tags);
		}

		$str = str_replace('<' . $old_tags[0] . '>', '<' . $new_tags[0] . '>', $str);
		$str = str_replace('</' . $old_tags[1] . '>', '</' . $new_tags[1] . '>', $str);

		return $str;
	}
}

/*
 * get inner html of a Dom node
 */
function get_inner_html( $node ) {
    $innerHTML= ''; 
    $children = $node->childNodes; 
    foreach ($children as $child) { 
        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    } 

    return $innerHTML;  
}

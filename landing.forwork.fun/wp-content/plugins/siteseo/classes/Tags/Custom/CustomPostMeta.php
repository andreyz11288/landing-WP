<?php
/*
* SiteSEO
* https://siteseo.io/
* (c) SiteSEO Team <support@siteseo.io>
*/

/*
Copyright 2016 - 2024 - Benjamin Denis  (email : contact@seopress.org)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace SiteSEO\Tags\Custom;

if ( ! defined('ABSPATH')) {
	exit;
}

use SiteSEO\Models\AbstractCustomTagValue;
use SiteSEO\Models\GetTagValue;

class CustomPostMeta extends AbstractCustomTagValue implements GetTagValue {
	const CUSTOM_FORMAT = '_cf_';
	const NAME		  = '_cf_your_custom_field_name';

	public static function getDescription() {
		return __('Custom fields (replace your_custom_field_name by the name of your custom field)', 'siteseo');
	}

	public function getValue($args = null) {
		$context = isset($args[0]) ? $args[0] : null;
		$tag	 = isset($args[1]) ? $args[1] : null;
		$value   = '';
		if (null === $tag || ! $context) {
			return $value;
		}

		if ( ! $context['post']) {
			return $value;
		}
		$regex = $this->buildRegex(self::CUSTOM_FORMAT);

		preg_match($regex, $tag, $matches);

		if (empty($matches) || ! array_key_exists('field', $matches)) {
			return $value;
		}

		$field = $matches['field'];

		$value = esc_attr(get_post_meta($context['post']->ID, $field, true));

		return apply_filters('siteseo_get_tag_' . $tag . '_value', $value, $context);
	}
}
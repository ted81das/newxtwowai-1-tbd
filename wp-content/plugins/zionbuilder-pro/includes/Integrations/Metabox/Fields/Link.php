<?php
namespace ZionBuilderPro\Integrations\Metabox\Fields;

use ZionBuilderPro\DynamicContent\BaseField;
use ZionBuilderPro\Integrations\Metabox\Traits\Base;

class Link extends BaseField {
	use Base;

	public function get_category() {
		return [
			BaseField::CATEGORY_LINK,
		];
	}

	public function get_id() {
		return 'meta-box-link';
	}
}

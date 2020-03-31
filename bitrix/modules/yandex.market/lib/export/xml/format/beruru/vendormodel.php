<?php

namespace Yandex\Market\Export\Xml\Format\BeruRu;

use Yandex\Market\Export\Xml;
use Yandex\Market\Type;

class VendorModel extends Xml\Format\YandexMarket\VendorModel
{
	public function getOffer()
	{
		$tag = parent::getOffer();

		$this->overrideTags($tag->getChildren(), [
			'picture' => [ 'required' => false ]
		]);

		$tag->addChild(new Xml\Tag\Vat(), 4);

		$tag->addChildren([
			new Xml\Tag\ShopSku(['required' => true]),
			new Xml\Tag\Base(['name' => 'market-sku']),
			new Xml\Tag\Base(['name' => 'disabled', 'value_type' => Type\Manager::TYPE_BOOLEAN]),
			new Xml\Tag\Count(),
		]);

		$this->removeChildTags($tag, ['condition', 'credit-template', 'purchase_price']);

		return $tag;
	}

	protected function removeChildTags(Xml\Tag\Base $tag, $tagNameList)
	{
		foreach ($tagNameList as $tagName)
		{
			$childTag = $tag->getChild($tagName);

			if ($childTag)
			{
				$tag->removeChild($childTag);
			}
		}
	}
}
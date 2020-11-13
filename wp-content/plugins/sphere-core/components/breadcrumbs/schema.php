<?php
namespace Sphere\Core\Breadcrumbs;
/**
 * Generate JSON+LD schema for breadcrumbs.
 */
class Schema 
{
	/**
	 * Breadcrumbs to use
	 *
	 * @var array
	 */
	public $crumbs = [];

	public function __construct($crumbs)
	{
		$this->crumbs = $crumbs;
	}

	/**
	 * Output breadcrumbs schema data.
	 *
	 * @return void
	 */
	public function render()
	{
		foreach ($this->crumbs as $index => $item) {
			if (!isset($item['text']) || !isset($item['url'])) {
				continue;
			}

			$items[] = [
				'@type'    => 'ListItem',
				'position' => ($index + 1),
				'name'  => $item['text'],
				'item'  => $item['url'],
			];
		}

		$schema = [
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		];

		echo '<script type="application/ld+json">' . json_encode($schema) . "</script>\n";
	}
}
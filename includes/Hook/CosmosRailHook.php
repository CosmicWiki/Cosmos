<?php

namespace MediaWiki\Skins\Cosmos\Hook;

use Skin;

interface CosmosRailHook {

	/**
	 * @param array &$modules
	 * @param Skin $skin
	 * @return void
	 */
	public function onCosmosRail( array &$modules, Skin $skin ): void;
}

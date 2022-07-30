<?php

namespace MediaWiki\Skins\Cosmos\Hook;

use MediaWiki\HookContainer\HookContainer;

class CosmosHookRunner implements CosmosRailHook {

	/**
	 * @var HookContainer
	 */
	private $container;

	/**
	 * @param HookContainer $container
	 */
	public function __construct( HookContainer $container ) {
		$this->container = $container;
	}

	/** @inheritDoc */
	public function onCosmosRail( array &$modules, Skin $skin ): void {
		$this->container->run( 'CosmosRail', [ &$modules, $skin ] );
	}
}

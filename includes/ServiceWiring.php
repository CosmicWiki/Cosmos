<?php

use MediaWiki\Config\ServiceOptions;
use MediaWiki\MediaWikiServices;
use RequestContext;
use MediaWiki\Skins\Cosmos\CosmosBackgroundLookup;
use MediaWiki\Skins\Cosmos\CosmosConfig;
use MediaWiki\Skins\Cosmos\CosmosRail;
use MediaWiki\Skins\Cosmos\CosmosWordmarkLookup;
use MediaWiki\Skins\Hook\CosmosHookRunner;

return [
	'CosmosConfig' => static function ( MediaWikiServices $services ): CosmosConfig {
		return new CosmosConfig(
			$services->getConfigFactory()->makeConfig( 'Cosmos' )
		);
	},

	'CosmosHookRunner' => static function ( MediaWikiServices $services ): CosmosHookRunner {
		return new CosmosHookRunner(
			$services->getHookContainer()
		);
	},

	'CosmosRail' => static function ( MediaWikiServices $services ): CosmosRail {
		return new CosmosRail(
			$services->getService( 'CosmosConfig' ),
			$services->getService( 'CosmosHookRunner' ),
			$services->getDBLoadBalancer(),
			$services->getLinkRenderer(),
			RequestContext::getMain(),
			new ServiceOptions(
				CosmosRail::CONSTRUCTOR_OPTIONS,
				$services->getConfigFactory()->makeConfig( 'Cosmos' )
			),
			$services->getSpecialPageFactory(),
			$services->getUserFactory(),
			$services->getMainWANObjectCache()
		);
	},

	'CosmosWordmarkLookup' => static function ( MediaWikiServices $services ): CosmosWordmarkLookup {
		return new CosmosWordmarkLookup(
			$services->getTitleFactory(),
			$services->getRepoGroup(),
			$services->getService( 'CosmosConfig' )->getWordmark()
		);
	},

	'CosmosBackgroundLookup' => static function ( MediaWikiServices $services ): CosmosBackgroundLookup {
		return new CosmosBackgroundLookup(
			$services->getTitleFactory(),
			$services->getRepoGroup(),
			$services->getService( 'CosmosConfig' )->getBackgroundImage(),
			$services->getService( 'CosmosConfig' )->getWikiHeaderBackgroundImage()
		);
	}
];

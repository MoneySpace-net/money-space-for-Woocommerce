const WooCommerceDependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );

module.exports = {
	// …snip
	plugins: [
        new WooCommerceDependencyExtractionWebpackPlugin( {
			bundledPackages: [ '@woocommerce/components' ],
		} ),
    ],
};
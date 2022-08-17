const path = require('path');
const webpack = require('webpack');
const AssetsPlugin = require('assets-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
  publicPath: '/citto/indicators_new/',
  assetsDir: 'assets',
  filenameHashing: false,
  productionSourceMap: false,
  chainWebpack: config => {
    config.resolve.alias.set(
      'vue$',
      path.resolve(__dirname, 'node_modules/vue/dist/vue.runtime.esm.js'),
    );
  },
  configureWebpack: {
    plugins: [
      new AssetsPlugin(),
      new CleanWebpackPlugin({
        cleanAfterEveryBuildPatterns: ['/assets/**/*', '!/dist/assets*'],
      }),
      new webpack.optimize.LimitChunkCountPlugin({
        maxChunks: 1,
      }),
    ],
  },
  devServer: {
    proxy: 'http://cit71.test/local/api/indicators',
  },
};

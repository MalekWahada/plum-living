const path = require('path');
const Encore = require('@symfony/webpack-encore');

const syliusBundles = path.resolve(__dirname, 'vendor/sylius/sylius/src/Sylius/Bundle/');
const uiBundleScripts = path.resolve(syliusBundles, 'UiBundle/Resources/private/js/');
const uiBundleResources = path.resolve(syliusBundles, 'UiBundle/Resources/private/');
const nodeModulesFolder = path.resolve(__dirname, 'node_modules/');
const frontAbstractFolder = path.resolve(__dirname, 'assets/abstract/');
const frontComponentsFolder = path.resolve(__dirname, 'assets/components/');
const frontConfigFolder = path.resolve(__dirname, 'assets/config/');
const frontRoutesFolder = path.resolve(__dirname, 'assets/routes/');

const stylerTranslationTool = require('@plum/styler/translation-tool');

// Shop config
Encore
  .setOutputPath('public/build/shop/')
  .setPublicPath('/build/shop')
  .addEntry('shop', [
    './assets/shop/shop.js',
    './assets/shop/shop.scss',
    './assets/shop/checkout.js',
  ])
  .disableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader((options) => {
    options.implementation = require('sass');
  })
  .enableTypeScriptLoader()
  .copyFiles({
    from: './assets/shop/images',
    pattern: /.*/,
    to: 'images/[path][name].[hash:8].[ext]',
  });

const shopConfig = Encore.getWebpackConfig();

if (!Encore.isProduction()) {
  shopConfig.entry.shop.push('./assets/shop/dev.scss');
}

shopConfig.resolve.alias['sylius/ui'] = uiBundleScripts;
shopConfig.resolve.alias['sylius/ui-resources'] = uiBundleResources;
shopConfig.resolve.alias['sylius/bundle'] = syliusBundles;

shopConfig.resolve.extensions.push('.scss');
shopConfig.resolve.alias.NodeModules = nodeModulesFolder;
shopConfig.resolve.alias.Abstract = frontAbstractFolder;
shopConfig.resolve.alias.Components = frontComponentsFolder;
shopConfig.resolve.alias.Config = frontConfigFolder;
shopConfig.resolve.alias.Routes = frontRoutesFolder;

shopConfig.name = 'shop';

Encore.reset();

// Admin config
Encore
  .setOutputPath('public/build/admin/')
  .setPublicPath('/build/admin')
  .addEntry('admin', [
    './assets/admin/admin.js',
    './assets/admin/admin.scss',
  ])
  .disableSingleRuntimeChunk()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader((options) => {
    options.implementation = require('sass');
  });

const adminConfig = Encore.getWebpackConfig();

adminConfig.resolve.alias['sylius/ui'] = uiBundleScripts;
adminConfig.resolve.alias['sylius/ui-resources'] = uiBundleResources;
adminConfig.resolve.alias['sylius/bundle'] = syliusBundles;
adminConfig.resolve.alias.NodeModules = nodeModulesFolder;
adminConfig.resolve.alias.Abstract = frontAbstractFolder;
adminConfig.resolve.alias.Components = frontComponentsFolder;
adminConfig.resolve.alias.Config = frontConfigFolder;
adminConfig.resolve.alias.Routes = frontRoutesFolder;
adminConfig.externals = { ...adminConfig.externals, window: 'window', document: 'document' };
adminConfig.name = 'admin';

Encore.reset();

// Styler config
Encore
  .enableReactPreset()
  .addEntry('styler', [
    './assets/styler/src/index.tsx',
    './assets/styler/src/index.scss',
  ])
  .setOutputPath('public/build/styler/')
  .setPublicPath('/build/styler')
  .disableSingleRuntimeChunk()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .enableSassLoader((options) => {
    options.implementation = require('sass');
  })
  .enableTypeScriptLoader();

const stylerConfig = Encore.getWebpackConfig();

stylerConfig.plugins.push({
  apply(compiler) {
    compiler.hooks.run.tap('StylerTranslationTool', () => {
      stylerTranslationTool.process(__dirname);
    });
  },
});
stylerConfig.name = 'styler';

module.exports = [shopConfig, adminConfig];

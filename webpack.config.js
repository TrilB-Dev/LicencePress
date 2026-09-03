const path = require('path');
const fs = require('fs');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const entries = {
  bootstrap: [
    './src/Assets/js/bootstrap.js',
    './src/Assets/scss/bootstrap.scss',
  ],
  'admin.ui': [
    './src/Assets/js/admin.ui.js',
    './src/Assets/scss/admin.ui.scss',
  ],
  'wpoverride': './src/Assets/scss/wpoverride.scss',
  'bootstrap-select': [
    './src/Assets/js/bootstrap-select.js',
    './src/Assets/scss/bootstrap-select.scss',
  ],
};

const fontAwesomeEntries = {
  'icon-picker': [
    './src/Includes/Plugins/FontAwesome/Assets/js/fontawesome.icon-picker.js',
    './src/Includes/Plugins/FontAwesome/Assets/scss/fontawesome.icon-picker.scss',
  ],
};
const tinyMCEEntries = {
  'tinyMCE': [
    './src/Includes/Plugins/TinyMCE/Assets/js/tinymce.js',
    './src/Includes/Plugins/TinyMCE/Assets/scss/tinymce.scss',
  ],
};
const paypalEntries = {
  'paypal.admin': [
    './src/Includes/Plugins/PayPal/Assets/js/paypal.admin.js',
    './src/Includes/Plugins/PayPal/Assets/scss/paypal.admin.scss',
  ],
  'paypal.frontend': [
    './src/Includes/Plugins/PayPal/Assets/js/paypal.frontend.js',
    './src/Includes/Plugins/PayPal/Assets/scss/paypal.frontend.scss',
  ],
};

const jsDirectory = path.resolve(__dirname, 'src/Assets/js');
fs.readdirSync(jsDirectory)
  .filter((file) => /^admin\.[^.]+\.js$/.test(file) && file !== 'admin.ui.js')
  .forEach((file) => {
    const page = file.match(/^admin\.([^.]+)\.js$/)[1];
    const entry = [`./src/Assets/js/${file}`];
    entries[`admin.${page}`] = entry;
  });

const shared = {
  mode: process.env.NODE_ENV === 'development' ? 'development' : 'production',
  devtool: process.env.NODE_ENV === 'development' ? 'source-map' : false,
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          {
            loader: 'sass-loader',
            options: {
              api: 'modern',
              sassOptions: {
                quietDeps: true,
                includePaths: [path.resolve(__dirname, 'src/Assets/scss')],
              },
            },
          },
        ],
      },
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        type: 'javascript/auto',
      },
    ],
  },
  optimization: { splitChunks: false },
};

module.exports = [
  {
    ...shared,
    entry: entries,
    output: {
      path: path.resolve(__dirname, 'src/Assets/dist'),
      filename: 'js/[name].js',
      clean: true,
    },
    plugins: [
      new MiniCssExtractPlugin({ filename: 'css/[name].css' }),
    ],
  },
  {
    ...shared,
    entry: fontAwesomeEntries,
    output: {
      path: path.resolve(__dirname, 'src/Includes/Plugins/FontAwesome/Assets/dist'),
      filename: 'js/[name].js',
      clean: true,
    },
    plugins: [
      new MiniCssExtractPlugin({ filename: 'css/[name].css' }),
    ],
  },
  {
    ...shared,
    entry: tinyMCEEntries,
    output: {
      path: path.resolve(__dirname, 'src/Includes/Plugins/TinyMCE/Assets/dist'),
      filename: 'js/[name].js',
      clean: true,
    },
    plugins: [
      new MiniCssExtractPlugin({ filename: 'css/[name].css' }),
    ],
  },
  {
    ...shared,
    entry: paypalEntries,
    output: {
      path: path.resolve(__dirname, 'src/Includes/Plugins/PayPal/Assets/dist'),
      filename: 'js/[name].js',
      clean: true,
    },
    plugins: [
      new MiniCssExtractPlugin({ filename: 'css/[name].css' }),
    ],
  }
];

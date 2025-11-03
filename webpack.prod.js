const path = require('path');
// const HtmlWebPackPlugin = require("html-webpack-plugin");
const webpack = require('webpack');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
// const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin'); // installed via npm
// const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');


module.exports = {
    mode: 'production',
    entry: {
        app: './src/index.js'
    },
    output: {
        filename: '[name].bundle.js',
        path: path.resolve(__dirname, 'dist')
    },
    watch: false,
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules\/(?!(dom7|ssr-window|swiper)\/).*/,
                use: {
                    loader: "babel-loader",
                    options: {
                        presets: [
                            "@babel/preset-env",
                        ],
                        plugins : ["transform-class-properties"]
                    }
                }
            },
            {
                test: /\.(glsl|vs|fs|vert|frag)$/,
                exclude: /node_modules/,
                use: [
                    'raw-loader',
                    'glslify-loader'
                ]
            },
            // {
            //     test: /\.html$/,
            //     use: [
            //         {
            //             loader: "html-loader",
            //             options: { minimize: true }
            //         }
            //     ]
            // },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader,
                    },
                    // "style-loader", // creates style nodes from JS strings
                    "css-loader", // translates CSS into CommonJS
                    "sass-loader", // compiles Sass to CSS, using Node Sass by default
                    // {
                    //     loader: 'sass-resources-loader',
                    //     options: {
                    //         resources: [
                    //             './src/styles/settings/_settings.scss', 
                    //             './src/styles/tools/_mixins.scss',
                    //             './src/styles/tools/_functions.scss'
                    //         ]
                    //     },
                    // }
                ],
            },
            {
                test: /\.(png|jpg|gif)$/,
                use: [
                    {
                        loader: 'url-loader',
                        options: {
                            limit: 50000
                        }
                    }
                ]
            },
            {
                test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
                use: [{
                    loader: 'file-loader',
                    options: {
                        name: '[name].[ext]',
                        outputPath: 'fonts/'
                    }
                }]
            }
        ]
    },
    plugins: [
        new CleanWebpackPlugin(),
        // new HtmlWebPackPlugin({
        //     template: "./src/index.html",
        //     filename: "./index.html"
        // }),
        new BrowserSyncPlugin({
            // browse to http://localhost:3000/ during development,
            // ./public directory is being served
            host: 'localhost',
            port: 3000,
            proxy: 'http://localhost/' + path.basename(__dirname) + '/', // 'http://localhost:8888/tigrelab2019/',
            files: [
                "dist/*.css", 
                "dist/*.js", 
                "**/*.php",
                "!node_modules/**/*.*",
                "!bower_components/**/*.*"
            ]
        }),
        new MiniCssExtractPlugin({
            filename: "[name].css",
            chunkFilename: "[id].css"
        }),        
        new webpack.ProvidePlugin({
            'THREE': 'three',
            PhotoSwipe: 'photoswipe',
            PhotoSwipeUI_Default: 'photoswipe/src/js/ui/photoswipe-ui-default.js'
		})
    ],
    optimization: {
        minimize: true,
        // splitChunks: {
        //     chunks: "all",
        //     minSize: 0
        // }
    },
};
module.exports = {
  plugins: [
    require('autoprefixer')({
      overrideBrowserslist: [
        'defaults',
        'not IE 11'
      ]
    }),
    require('cssnano')({
      preset: ['default', {
        discardComments: {
          removeAll: true
        },
        normalizeWhitespace: true,
        minifySelectors: true,
        minifyParams: true,
        mergeLonghand: true,
        mergeRules: true,
        uniqueSelectors: true
      }]
    })
  ]
}

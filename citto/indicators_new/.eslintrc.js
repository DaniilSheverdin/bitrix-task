module.exports = {
  root: true,
  env: {
    node: true,
  },
  extends: [
    'plugin:vue/essential',
    '@vue/airbnb',
  ],
  parserOptions: {
    parser: 'babel-eslint',
  },
  rules: {
    'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    'import/extensions': ['error', 'always', {
      js: 'never',
      mjs: 'never',
      jsx: 'never',
      ts: 'never',
      tsx: 'never',
      vue: 'never'
    }],
    'arrow-parens': [2, 'as-needed'],
    'max-len': ['warn', { 'code': 130 }],
    'no-tabs': 0,
    'camelcase': 'off',
    'no-param-reassign': 'off',
    'no-return-assign': 'off',
    'no-prototype-builtins': 'off',
    'consistent-return': 'off',
    'vue/return-in-computed-property': 'off',
    'linebreak-style': 0,
    'quote-props': 0,
  },
};

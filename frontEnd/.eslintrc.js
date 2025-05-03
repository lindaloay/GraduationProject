module.exports = {
  root: true,
  env: {
    node: true,
    browser: true,
    es2021: true
  },
  extends: [],
  parserOptions: {
    parser: '@babel/eslint-parser',
    ecmaVersion: 2021
  },
  rules: {
    'no-console': 'off',
    'no-debugger': 'off',
    'vue/multi-word-component-names': 'off'
  }
} 
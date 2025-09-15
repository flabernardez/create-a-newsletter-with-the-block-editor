module.exports = {
    env: {
        browser: true,
        es6: true,
        node: true,
    },
    parserOptions: {
        ecmaVersion: 2020,
        sourceType: 'module',
        ecmaFeatures: {
            jsx: true,
        },
    },
    rules: {
        'no-console': 'warn',
        'no-unused-vars': 'warn',
        'indent': ['error', 4],
        'quotes': ['error', 'single'],
        'semi': ['error', 'always'],
    },
    globals: {
        wp: 'readonly',
        canwbeRoles: 'readonly',
        canwbeConfig: 'readonly',
        jQuery: 'readonly',
        ajaxurl: 'readonly',
    },
};

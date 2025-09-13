module.exports = {
    extends: ['@wordpress/eslint-config'],
    env: {
        browser: true,
        es6: true,
    },
    parserOptions: {
        ecmaVersion: 2020,
        sourceType: 'module',
        ecmaFeatures: {
            jsx: true,
        },
    },
    rules: {
        // Personaliza las reglas según tus preferencias
        'no-console': 'warn',
        '@wordpress/no-unused-vars-before-return': 'error',
    },
    globals: {
        wp: 'readonly',
        canwbeRoles: 'readonly',
    },
};

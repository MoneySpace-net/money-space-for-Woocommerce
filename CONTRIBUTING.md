# Contributing to MoneySpace Payment Gateway for WooCommerce

Thank you for your interest in contributing to the MoneySpace Payment Gateway for WooCommerce! This document provides guidelines and information for contributors.

## 🎯 How to Contribute

### Types of Contributions We Welcome
- 🐛 **Bug Reports** - Help us identify and fix issues
- ✨ **Feature Requests** - Suggest new functionality
- 📝 **Documentation** - Improve guides and documentation
- 🔧 **Code Contributions** - Submit bug fixes and new features
- 🌐 **Translations** - Help localize the plugin
- 🧪 **Testing** - Help test new releases and features

## 🚀 Getting Started

### 1. Fork and Clone the Repository
```bash
# Fork the repository on GitHub
# Then clone your fork
git clone https://github.com/YOUR_USERNAME/money-space-for-woocommerce.git
cd money-space-for-woocommerce

# Add upstream remote
git remote add upstream https://github.com/MoneySpace-net/money-space-for-woocommerce.git
```

### 2. Set Up Development Environment
```bash
# Install Node.js dependencies
npm install

# Install PHP dependencies (if using Composer)
composer install

# Set up local WordPress/WooCommerce environment
# We recommend using Local by Flywheel, XAMPP, or Docker
```

### 3. Create a Feature Branch
```bash
# Update your fork
git fetch upstream
git checkout main
git merge upstream/main

# Create a new branch for your feature/fix
git checkout -b feature/your-feature-name
# or
git checkout -b bugfix/issue-description
```

## 📋 Development Guidelines

### Code Standards

#### PHP Standards
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Use PHP 8.0+ features where appropriate
- Always sanitize and validate user input
- Use WordPress functions for database operations, HTTP requests, etc.

```php
// Good example
$secret_id = sanitize_text_field($_POST['secret_id']);
$response = wp_remote_post($url, $args);

if (is_wp_error($response)) {
    return new WP_Error('api_error', $response->get_error_message());
}
```

#### JavaScript/React Standards
- Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- Use modern ES6+ syntax
- Implement proper error boundaries for React components
- Use @wordpress/element instead of React directly

```javascript
// Good example
import { useState, useEffect, useCallback } from '@wordpress/element';

const MyComponent = ({ prop1, prop2 }) => {
    const [state, setState] = useState('');
    
    const handleChange = useCallback((value) => {
        setState(value);
    }, []);
    
    return <div>{state}</div>;
};
```

#### CSS/SCSS Standards
- Use BEM methodology for class naming
- Follow WordPress CSS standards
- Use CSS custom properties for theming
- Ensure mobile responsiveness

```scss
// Good example
.wc-block-components-payment-form {
    &__header {
        font-weight: 600;
        margin-bottom: var(--moneyspace-spacing-unit);
    }
    
    &__option {
        border: 1px solid var(--moneyspace-border-color);
        
        &--selected {
            border-color: var(--moneyspace-primary-color);
        }
    }
}
```

### Testing Requirements

#### PHP Testing
```bash
# Run PHPUnit tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/Unit/PaymentGatewayTest.php

# Generate coverage report
./vendor/bin/phpunit --coverage-html coverage/
```

#### JavaScript Testing
```bash
# Run Jest tests
npm test

# Run tests in watch mode
npm run test:watch

# Run specific test file
npm test CreditCardInstallmentForm.test.js
```

#### Manual Testing Checklist
- [ ] Plugin activates without errors
- [ ] Payment methods appear in WooCommerce settings
- [ ] Configuration forms work correctly
- [ ] Payment processing works in test mode
- [ ] Webhooks are received and processed
- [ ] Order statuses update correctly
- [ ] Mobile responsive design works
- [ ] Browser compatibility (Chrome, Firefox, Safari, Edge)

### Documentation Standards

#### Code Documentation
```php
/**
 * Process payment transaction for MoneySpace gateway
 *
 * @since 2.13.0
 * @param WC_Order $order The WooCommerce order object
 * @param array    $payment_data Payment form data
 * @return array   Payment processing result
 * @throws Exception When API communication fails
 */
public function process_payment_transaction(WC_Order $order, array $payment_data): array {
    // Implementation
}
```

#### README Updates
- Update feature descriptions if adding new functionality
- Include new configuration options in setup guides
- Add troubleshooting information for new features
- Update version compatibility information

## 🐛 Bug Reports

### Before Submitting a Bug Report
1. **Search existing issues** to avoid duplicates
2. **Test with latest version** of the plugin
3. **Disable other plugins** to check for conflicts
4. **Check error logs** for relevant error messages

### Bug Report Template
```markdown
**Bug Description**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

**Expected Behavior**
What you expected to happen.

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Environment:**
- WordPress version: [e.g. 6.4]
- WooCommerce version: [e.g. 8.5]
- Plugin version: [e.g. 2.13.3]
- PHP version: [e.g. 8.1]
- Browser: [e.g. Chrome 120]

**Error Logs**
```
Paste any relevant error logs here
```

**Additional Context**
Add any other context about the problem here.
```

## ✨ Feature Requests

### Before Submitting a Feature Request
1. **Search existing issues** for similar requests
2. **Consider the scope** - does it fit the plugin's purpose?
3. **Think about backwards compatibility**
4. **Consider maintenance burden**

### Feature Request Template
```markdown
**Feature Description**
A clear description of what feature you'd like to see.

**Problem Statement**
What problem would this feature solve? What's the current workaround?

**Proposed Solution**
Describe how you envision this feature working.

**Alternatives Considered**
What other approaches have you considered?

**Use Cases**
Provide specific examples of how this feature would be used.

**Implementation Notes**
If you have technical insights, share them here.
```

## 🔄 Pull Request Process

### 1. Pre-Submission Checklist
- [ ] Code follows established standards
- [ ] All tests pass locally
- [ ] Documentation is updated
- [ ] Commit messages are clear and descriptive
- [ ] Branch is up-to-date with main

### 2. Pull Request Template
```markdown
## Description
Brief description of changes made.

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] Manual testing completed
- [ ] Cross-browser testing completed

## Screenshots
If applicable, add screenshots of your changes.

## Checklist
- [ ] My code follows the style guidelines
- [ ] I have performed a self-review of my code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes
```

### 3. Review Process
1. **Automated checks** must pass (CI/CD pipeline)
2. **Code review** by maintainers
3. **Testing** on development environment
4. **Approval** and merge by maintainers

## 🌐 Translation Contributions

### Adding New Translations
1. **Create language files** using Poedit or similar tool
2. **Translate strings** in context
3. **Test translations** in WordPress
4. **Submit pull request** with new language files

### Translation Guidelines
- Use formal tone for official communications
- Be consistent with terminology
- Consider cultural context
- Test translations in actual interface

## 📦 Release Process

### Version Numbering
We follow [Semantic Versioning](https://semver.org/):
- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality additions
- **PATCH** version for backwards-compatible bug fixes

### Release Checklist
- [ ] Update version numbers in plugin files
- [ ] Update CHANGELOG.md
- [ ] Create release notes
- [ ] Tag release in Git
- [ ] Create GitHub release
- [ ] Test release package
- [ ] Announce release

## 🤝 Community Guidelines

### Code of Conduct
- **Be respectful** and inclusive
- **Be constructive** in feedback
- **Be patient** with new contributors
- **Be professional** in all communications

### Communication Channels
- **GitHub Issues** - Bug reports and feature requests
- **GitHub Discussions** - General questions and ideas
- **Email** - security@moneyspace.net for security issues

## 🏆 Recognition

### Contributors
All contributors will be:
- **Listed** in the contributors section
- **Credited** in release notes for significant contributions
- **Thanked** publicly for their efforts

### Types of Recognition
- **Bug Hunter** - Finding and reporting bugs
- **Feature Architect** - Designing new features
- **Code Contributor** - Submitting code improvements
- **Documentation Expert** - Improving documentation
- **Translation Hero** - Adding language support

## 📞 Getting Help

### Development Questions
- **GitHub Discussions** - Ask questions about contributing
- **Code Review** - Request feedback on your approach
- **Architecture Discussion** - Discuss major changes before implementing

### Contact Information
- **General Questions**: github@moneyspace.net
- **Security Issues**: security@moneyspace.net
- **Technical Discussion**: Create a GitHub Discussion

## 🙏 Thank You

Thank you for contributing to the MoneySpace Payment Gateway for WooCommerce! Your efforts help make online payments better for businesses across Thailand and beyond.

Every contribution, no matter how small, makes a difference. Whether you're fixing a typo, reporting a bug, or adding a major feature, we appreciate your help in making this project better.

---

**Happy Contributing! 🚀**

*The MoneySpace Development Team*

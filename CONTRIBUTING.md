# Contributing to Force Sensitivity Detector

Thank you for your interest in contributing to the Force Sensitivity Detector extension! This document provides guidelines for contributing to the project.

**Current Version**: v1.0.0 (Released January 17, 2026)

---

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for everyone.

---

## How to Contribute

### Reporting Bugs

1. **Search existing issues** to avoid duplicates
2. **Use the bug report template** when creating a new issue
3. **Include**:
   - ICS version
   - PHP version
   - Extension version
   - Steps to reproduce
   - Expected vs actual behavior
   - Screenshots if applicable

### Suggesting Features

1. **Search existing issues** for similar suggestions
2. **Use the feature request template**
3. **Describe**:
   - The problem you're trying to solve
   - Your proposed solution
   - Alternative approaches considered

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/your-feature-name`
3. **Make your changes**
4. **Test thoroughly**
5. **Commit with clear messages**
6. **Push to your fork**
7. **Open a Pull Request**

---

## Development Setup

### Prerequisites

- PHP 7.4+ (8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.2+
- Composer
- Local ICS v4.7.20 installation

### Setup Steps

1. Clone your fork:
   ```bash
   git clone https://github.com/YOUR_USERNAME/ICS-ext-forcesensitivity.git
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Link to your ICS installation:
   ```bash
   ln -s /path/to/extension /path/to/ips/applications/forcesensitivity
   ```

4. Enable developer mode in ICS

5. Install the application via ACP

---

## Coding Standards

### PHP

- Follow PSR-12 coding style
- Use strict types when possible
- Document all public methods with PHPDoc
- Keep methods focused and small
- Write unit tests for new functionality

### Example

```php
<?php
declare(strict_types=1);

namespace IPS\forcesensitivity;

/**
 * Example class demonstrating coding standards
 */
class Example
{
    /**
     * @var string Description of property
     */
    protected string $property;

    /**
     * Constructor
     *
     * @param string $value Initial value
     */
    public function __construct(string $value)
    {
        $this->property = $value;
    }

    /**
     * Get the property value
     *
     * @return string The current value
     */
    public function getProperty(): string
    {
        return $this->property;
    }
}
```

### JavaScript

- Use ES6+ features
- Follow ICS JavaScript patterns
- Document complex functions
- Avoid global variables

### CSS

- Use BEM naming convention
- Prefix classes with `fs-`
- Keep specificity low
- Support dark/light themes

---

## Commit Messages

Use clear, descriptive commit messages:

```
type(scope): brief description

Longer explanation if needed. Wrap at 72 characters.

Fixes #123
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only
- `style`: Formatting, no code change
- `refactor`: Code restructuring
- `test`: Adding/modifying tests
- `chore`: Maintenance tasks

### Examples

```
feat(detector): add custom probability modifier hooks
fix(admin): correct pagination on member list
docs(readme): update installation instructions
```

---

## Testing

### Running Tests

```bash
./vendor/bin/phpunit tests/
```

### Writing Tests

- Place tests in `/tests` directory
- Mirror source structure
- Name test methods descriptively
- Test edge cases

### Example Test

```php
<?php

namespace IPS\forcesensitivity\tests;

use PHPUnit\Framework\TestCase;

class DetectorTest extends TestCase
{
    public function testProbabilityCalculationWithinBounds(): void
    {
        $detector = new \IPS\forcesensitivity\ForceSensitivity\Detector();
        $probability = $detector->calculateProbability($this->mockMember());
        
        $this->assertGreaterThanOrEqual(0.01, $probability);
        $this->assertLessThanOrEqual(0.50, $probability);
    }
}
```

---

## Review Process

1. All PRs require at least one approval
2. CI checks must pass
3. Documentation must be updated if needed
4. Breaking changes need discussion first

---

## Questions?

- Open a discussion on GitHub
- Check existing documentation
- Review closed issues for similar questions

---

Thank you for contributing! 🌟

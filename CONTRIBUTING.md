# Contributing to Asa Electronics

Thank you for your interest in contributing to Asa Electronics! This document provides guidelines for contributing to the project.

## Code of Conduct

Please be respectful and considerate of others when contributing to this project.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:
- A clear description of the problem
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- Screenshots (if applicable)
- Your environment (OS, PHP version, Node version, etc.)

### Suggesting Features

Feature suggestions are welcome! Please create an issue with:
- A clear description of the feature
- Why this feature would be useful
- How the feature should work
- Any implementation ideas

### Code Contributions

1. **Fork the Repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/AsaElectronics.git
   cd AsaElectronics
   ```

2. **Create a Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Your Changes**
   - Write clean, readable code
   - Follow existing code style
   - Add comments where necessary
   - Update documentation if needed

4. **Test Your Changes**
   - Test thoroughly before submitting
   - Ensure no existing functionality is broken
   - Test on different browsers (for frontend changes)

5. **Commit Your Changes**
   ```bash
   git add .
   git commit -m "Add feature: your feature description"
   ```

6. **Push to Your Fork**
   ```bash
   git push origin feature/your-feature-name
   ```

7. **Create a Pull Request**
   - Go to the original repository
   - Click "New Pull Request"
   - Select your branch
   - Provide a clear description of your changes

## Coding Standards

### PHP Code

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add PHPDoc comments for functions
- Always use prepared statements for database queries
- Validate and sanitize all user input
- Handle errors gracefully

Example:
```php
/**
 * Get user by email
 * 
 * @param string $email User email address
 * @return array|false User data or false if not found
 */
public function getUserByEmail($email) {
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetch();
}
```

### JavaScript/React Code

- Use ES6+ features
- Use functional components with hooks
- Use meaningful component and variable names
- Add JSDoc comments for complex functions
- Keep components small and focused
- Use proper error handling

Example:
```javascript
/**
 * Fetches products from the API
 * @param {Object} filters - Filter options
 * @returns {Promise<Array>} Array of products
 */
const fetchProducts = async (filters = {}) => {
  try {
    const response = await api.get('/products', { params: filters });
    return response.data.products;
  } catch (error) {
    console.error('Failed to fetch products:', error);
    throw error;
  }
};
```

### CSS

- Use CSS variables for colors and common values
- Use meaningful class names
- Keep styles modular and reusable
- Ensure responsive design
- Test on different screen sizes

### Database

- Use meaningful table and column names
- Always add indexes for foreign keys
- Add appropriate constraints
- Document complex queries
- Use transactions for multi-step operations

## Security Guidelines

- Never commit sensitive data (passwords, API keys, etc.)
- Always use environment variables for configuration
- Validate and sanitize all user input
- Use prepared statements for database queries
- Implement proper authentication and authorization
- Keep dependencies up to date
- Follow OWASP security best practices

## Documentation

- Update README.md if you add new features
- Update API.md for API changes
- Add inline comments for complex logic
- Update deployment documentation if needed

## Testing

While we don't have automated tests yet, please:
- Manually test all changes thoroughly
- Test on different browsers
- Test on different screen sizes
- Test error cases
- Test edge cases

## Pull Request Process

1. Update documentation
2. Ensure code follows style guidelines
3. Test thoroughly
4. Create clear commit messages
5. Provide detailed PR description
6. Link related issues
7. Wait for review
8. Address review comments
9. Merge when approved

## Questions?

If you have questions, feel free to:
- Create an issue
- Contact the maintainers
- Check existing documentation

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

Thank you for contributing to Asa Electronics! 🎉

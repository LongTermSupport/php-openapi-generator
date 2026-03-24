FROM claude-yolo:latest

# ============================================================================
# PHP 8.4 Full Development Environment
# ============================================================================
# Project: lts/php-openapi-generator
# Requires: PHP ^8.4, ext-json
# QA pipeline: lts/php-qa-ci (vendor/bin/qa)
#
# Why PHP 8.4?
# - composer.json declares "php": "^8.4"
# - Uses PHP 8.4 typed properties, readonly, etc.
#
# Why Xdebug?
# - PHPUnit code coverage (used by infection mutation testing)
# - lts/php-qa-ci phpNoXdebug wrapper handles disabling it for static analysis
#
# PHP source: deb.sury.org (official PHP maintainer for Debian/Ubuntu)
# ============================================================================

# Add sury.org PHP repository (official Debian PHP 8.4 source)
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        gnupg \
        lsb-release \
    && curl -sSLo /tmp/sury.gpg https://packages.sury.org/php/apt.gpg \
    && install -D -m 644 /tmp/sury.gpg /etc/apt/keyrings/sury-php.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" \
        > /etc/apt/sources.list.d/sury-php.list \
    && rm -rf /var/lib/apt/lists/*

# Install PHP 8.4 CLI + all extensions needed by this project and its QA tools
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && \
    apt-get install -y --no-install-recommends \
        # Core runtime
        # Note: json, openssl, tokenizer, ctype, iconv, dom are bundled into
        # php8.4-cli / php8.4-xml in PHP 8.4 — no separate packages exist
        php8.4-cli \
        php8.4-common \
        # Required by Symfony (serializer, console, validator, etc.)
        # php8.4-xml also provides: dom, simplexml, xmlreader, xmlwriter
        php8.4-mbstring \
        php8.4-xml \
        # Required by league/uri and HTTP clients
        php8.4-curl \
        # Required by symfony/uid and other components
        php8.4-intl \
        # Required by phpbench and some test utilities
        php8.4-zip \
        # Required by php-jsonpointer and JSON schema validation
        php8.4-bcmath \
        # Xdebug — for PHPUnit coverage and infection mutation testing
        php8.4-xdebug \
        # PCOV — fast line coverage alternative (QA CI may prefer this)
        php8.4-pcov \
        # Build tools sometimes needed by PECL extensions
        php8.4-dev \
    && rm -rf /var/lib/apt/lists/*

# Create canonical `php` symlink so QA pipeline can find the binary
# lts/php-qa-ci uses: ${PHP_QA_CI_PHP_EXECUTABLE:-$(which php)}
RUN update-alternatives --set php /usr/bin/php8.4 && \
    php --version

# ============================================================================
# Composer (latest stable)
# ============================================================================
RUN curl -sSL https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin \
        --filename=composer && \
    composer --version

# ============================================================================
# Verify full toolchain
# ============================================================================
RUN php --version && \
    php -m | grep -E "json|mbstring|xml|curl|xdebug|pcov|intl" && \
    composer --version

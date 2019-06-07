#!/bin/bash

set -eu

# Docker
sudo apt install -y  curl \
 && curl -fsSL https://get.docker.com -o get-docker.sh \
 && sh get-docker.sh \
 && printf 'Waiting for Docker to start...\n\n' \
 && sleep 3

# Docker Compose
sudo wget \
        --output-document=/usr/local/bin/docker-compose \
        https://github.com/docker/compose/releases/download/1.24.0/run.sh \
    && sudo chmod +x /usr/local/bin/docker-compose \
    && sudo wget \
        --output-document=/etc/bash_completion.d/docker-compose \
        "https://raw.githubusercontent.com/docker/compose/$(docker-compose version --short)/contrib/completion/bash/docker-compose" \
    && printf '\nDocker Compose installed successfully\n\n'

# Git
sudo apt install git \
    && chmod 0777 /var/www \
    && cd /var/www \
    && /usr/bin/git clone https://github.com/karimovrr/rest-m.git \
    && cd /var/www/rest-m \
    && /usr/local/bin/docker-compose up -d \
    && /usr/bin/docker exec php /var/www/development.sh

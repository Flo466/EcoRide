# Use the stable Alpine-based Nginx for a lightweight production image
FROM nginx:stable-alpine

# Set the working directory for Nginx default content
WORKDIR /usr/share/nginx/html

# Replace the default Nginx configuration with our custom production config
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy only the necessary public files to the web root
# This prevents exposing sensitive source code or hidden git files
COPY index.html ./
COPY js/ ./js/
COPY assets/ ./assets/
COPY pages/ ./pages/
COPY Router/ ./Router/
COPY scss/ ./scss/

# Expose standard HTTP port
EXPOSE 80

# Run Nginx in the foreground to keep the container active
CMD ["nginx", "-g", "daemon off;"]
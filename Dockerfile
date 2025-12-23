FROM nginx:stable-alpine

# Set the working directory
WORKDIR /usr/share/nginx/html

# Copy your custom nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy all frontend files
COPY . .

# Expose port 80
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
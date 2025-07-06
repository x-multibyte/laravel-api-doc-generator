<?php

namespace LaravelApiDocs\Tests\Factories;

class ScenarioFactory extends BaseFactory
{
    protected array $states = [
        'simple_api' => [],
        'complex_api' => [],
        'microservice' => [],
        'e_commerce' => [],
        'blog' => [],
        'social_media' => [],
        'minimal' => [],
        'enterprise' => []
    ];

    protected function getDefaults(): array
    {
        return [
            'name' => 'Default API Scenario',
            'description' => 'A default API scenario for testing',
            'spec' => OpenApiSpecFactory::new()->make(),
            'routes' => [],
            'config' => []
        ];
    }

    public function make(): array
    {
        return $this->attributes;
    }

    public function simpleApi(): static
    {
        $this->attributes = [
            'name' => 'Simple API',
            'description' => 'A simple REST API with basic CRUD operations',
            'spec' => OpenApiSpecFactory::new()
                ->withPaths(3)
                ->state('minimal')
                ->make(),
            'routes' => array_merge(
                RouteFactory::createCrudRoutes('users'),
                RouteFactory::createCrudRoutes('posts')
            ),
            'config' => [
                'title' => 'Simple API',
                'version' => '1.0.0',
                'description' => 'A simple REST API'
            ]
        ];
        
        return $this;
    }

    public function complexApi(): static
    {
        $this->attributes = [
            'name' => 'Complex API',
            'description' => 'A complex API with authentication, pagination, and advanced features',
            'spec' => OpenApiSpecFactory::new()
                ->withPaths(15)
                ->state('complex')
                ->state('with_security')
                ->make(),
            'routes' => $this->generateComplexRoutes(),
            'config' => [
                'title' => 'Complex API',
                'version' => '2.0.0',
                'description' => 'A complex API with advanced features'
            ]
        ];
        
        return $this;
    }

    public function microservice(): static
    {
        $this->attributes = [
            'name' => 'Microservice API',
            'description' => 'A microservice API focused on a single domain',
            'spec' => OpenApiSpecFactory::new()
                ->withPaths(8)
                ->make(),
            'routes' => RouteFactory::createCrudRoutes('orders'),
            'config' => [
                'title' => 'Order Service API',
                'version' => '1.2.0',
                'description' => 'Microservice for order management'
            ]
        ];
        
        return $this;
    }

    public function eCommerce(): static
    {
        $this->attributes = [
            'name' => 'E-Commerce API',
            'description' => 'Complete e-commerce API with products, orders, and payments',
            'spec' => OpenApiSpecFactory::new()
                ->withPaths(25)
                ->state('complex')
                ->state('with_security')
                ->make(),
            'routes' => $this->generateECommerceRoutes(),
            'config' => [
                'title' => 'E-Commerce API',
                'version' => '3.0.0',
                'description' => 'Complete e-commerce platform API'
            ]
        ];
        
        return $this;
    }

    public function blog(): static
    {
        $this->attributes = [
            'name' => 'Blog API',
            'description' => 'Blog API with posts, comments, and categories',
            'spec' => OpenApiSpecFactory::new()
                ->withPaths(12)
                ->make(),
            'routes' => $this->generateBlogRoutes(),
            'config' => [
                'title' => 'Blog API',
                'version' => '1.5.0',
                'description' => 'Blog management API'
            ]
        ];
        
        return $this;
    }

    public function socialMedia(): static
    {
        $this->attributes = [
            'name' => 'Social Media API',
            'description' => 'Social media API with users, posts, and interactions',
            'spec' => OpenApiSpecFactory::new()
                ->withPaths(20)
                ->state('complex')
                ->state('with_security')
                ->make(),
            'routes' => $this->generateSocialMediaRoutes(),
            'config' => [
                'title' => 'Social Media API',
                'version' => '2.1.0',
                'description' => 'Social media platform API'
            ]
        ];
        
        return $this;
    }

    public function minimal(): static
    {
        $this->attributes = [
            'name' => 'Minimal API',
            'description' => 'Minimal API with just a few endpoints',
            'spec' => OpenApiSpecFactory::new()
                ->state('minimal')
                ->make(),
            'routes' => [
                RouteFactory::new()
                    ->withUri('api/health')
                    ->withMethods(['GET'])
                    ->withAction('HealthController@check')
                    ->make()
            ],
            'config' => [
                'title' => 'Minimal API',
                'version' => '0.1.0',
                'description' => 'A minimal API'
            ]
        ];
        
        return $this;
    }

    public function enterprise(): static
    {
        $this->attributes = [
            'name' => 'Enterprise API',
            'description' => 'Large enterprise API with comprehensive features',
            'spec' => OpenApiSpecFactory::new()
                ->withPaths(50)
                ->state('complex')
                ->state('with_security')
                ->make(),
            'routes' => $this->generateEnterpriseRoutes(),
            'config' => [
                'title' => 'Enterprise API',
                'version' => '4.0.0',
                'description' => 'Comprehensive enterprise API'
            ]
        ];
        
        return $this;
    }

    protected function generateComplexRoutes(): array
    {
        return array_merge(
            RouteFactory::createCrudRoutes('users'),
            RouteFactory::createCrudRoutes('posts'),
            RouteFactory::createCrudRoutes('comments'),
            RouteFactory::createCrudRoutes('categories'),
            [
                RouteFactory::new()
                    ->withUri('api/auth/login')
                    ->withMethods(['POST'])
                    ->withAction('AuthController@login')
                    ->make(),
                RouteFactory::new()
                    ->withUri('api/auth/logout')
                    ->withMethods(['POST'])
                    ->withAction('AuthController@logout')
                    ->authenticated()
                    ->make(),
                RouteFactory::new()
                    ->withUri('api/users/{id}/posts')
                    ->withMethods(['GET'])
                    ->withAction('UserController@posts')
                    ->withParameters(['id'])
                    ->make()
            ]
        );
    }

    protected function generateECommerceRoutes(): array
    {
        return array_merge(
            RouteFactory::createCrudRoutes('products'),
            RouteFactory::createCrudRoutes('categories'),
            RouteFactory::createCrudRoutes('orders'),
            RouteFactory::createCrudRoutes('customers'),
            [
                RouteFactory::new()
                    ->withUri('api/cart')
                    ->withMethods(['GET'])
                    ->withAction('CartController@show')
                    ->authenticated()
                    ->make(),
                RouteFactory::new()
                    ->withUri('api/cart/items')
                    ->withMethods(['POST'])
                    ->withAction('CartController@addItem')
                    ->authenticated()
                    ->make(),
                RouteFactory::new()
                    ->withUri('api/checkout')
                    ->withMethods(['POST'])
                    ->withAction('CheckoutController@process')
                    ->authenticated()
                    ->make(),
                RouteFactory::new()
                    ->withUri('api/payments')
                    ->withMethods(['POST'])
                    ->withAction('PaymentController@process')
                    ->authenticated()
                    ->make()
            ]
        );
    }

    protected function generateBlogRoutes(): array
    {
        return array_merge(
            RouteFactory::createCrudRoutes('posts'),
            RouteFactory::createCrudRoutes('comments'),
            RouteFactory::createCrudRoutes('categories'),
            RouteFactory::createCrudRoutes('tags'),
            [
                RouteFactory::new()
                    ->withUri('api/posts/{id}/comments')
                    ->withMethods(['GET'])
                    ->withAction('PostController@comments')
                    ->withParameters(['id'])
                    ->make(),
                RouteFactory::new()
                    ->withUri('api/posts/published')
                    ->withMethods(['GET'])
                    ->withAction('PostController@published')
                    ->make()
            ]
        );
    }

    protected function generateSocialMediaRoutes(): array
    {
        return array_merge(
            RouteFactory::createCrudRoutes('users'),
            RouteFactory::createCrudRoutes('posts'),
            RouteFactory::createCrudRoutes('comments'),
            [
                RouteFactory::new()
                    ->withUri('api/users/{id}/follow')
                    ->withMethods(['POST'])
                    ->withAction('UserController@follow')
                    ->withParameters(['id'])
                    ->authenticated()
                    ->make(),
                RouteFactory::new()
                    ->withUri('api/posts/{id}/like')
                    ->withMethods(['POST'])
                    ->withAction('PostController@like')
                    ->withParameters(['id'])
                    ->authenticated()
                    ->make(),
                RouteFactory::new()
                    ->withUri('api/feed')
                    ->withMethods(['GET'])
                    ->withAction('FeedController@index')
                    ->authenticated()
                    ->make()
            ]
        );
    }

    protected function generateEnterpriseRoutes(): array
    {
        $routes = [];
        $resources = [
            'users', 'roles', 'permissions', 'departments', 'projects',
            'tasks', 'reports', 'analytics', 'notifications', 'settings'
        ];
        
        foreach ($resources as $resource) {
            $routes = array_merge($routes, RouteFactory::createCrudRoutes($resource));
        }
        
        return $routes;
    }
}

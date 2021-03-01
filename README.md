# Abandoned
This package is abandoned. I am no longer actively developing for a Lighthouse application, thus I personally don't require testing tools for it anymore. Furthermore, most of my work was done in collaboration with the wonderful Lighthouse authors, and some of my ideas and code have found their way upstream. Take a look at [the documentation](https://lighthouse-php.com/5.2/testing/phpunit.html#setup) for more information about that.

# Laravel Lighthouse Test
This package provides some tools to help you test GraphQL APIs developed using [Lighthouse](https://www.lighthouse-php.com/). The library is targeted at Lighthouse and will not work with other libraries.

## Installation
You can install this library with composer.

```
composer require --dev knevelina/laravel-lighthouse-test
```

That's it, the package is now installed. It will do nothing yet until you add the traits to your tests. Alternatively, you can use the traits in your base `TestCase` to be able to use the functionality everywhere.

## Usage

### Constructing queries and mutations
This feature requires the `BuildsGraphQLQueries` trait:

```php
<?php
namespace Tests\Feature;

use Knevelina\LighthouseTest\Traits\BuildsGraphQLQueries;
use Tests\TestCase;

class FeatureTest extends TestCase
{
    use BuildsGraphQLQueries;
}
```

Now you can create queries and mutations programmatically using the `makeGraphQLQuery` and  `makeGraphQLMutation` methods. They both take three arguments:

- `string $field`: The name of the member of `Query` or `Mutation` you wish to query or mutate.
- `array $arguments`: Arguments to pass to the query or mutation.
- `array $selection`: Fields you wish to select from the result.

If `$selection` is `null`, `$arguments` is considered to contain the selection. In other words, the `$arguments` argument is optional.

The methods both return an instance of `Knevelina\LighthouseTest\Schema\GraphQLQuery`. You can now use this instance to run various tests against your API.

What makes this feature useful, is the fact that you can now create "template" queries and mutations, just like the users of your API will do, where you can test the query with different variables. Simply substitute the raw value in an argument with an instance of the `Variable` class.

#### Example
Consider an application with the following, simple GraphQL schema:

```graphql
type Reverse {
    result: String!
}
type Query {
    "Return the reverse of str."
    reverse(str: String!): Reverse!
}
```

```php
<?php
namespace Tests\Feature;

use Knevelina\LighthouseTest\Traits\BuildsGraphQLQueries;
use Tests\TestCase;

class FeatureTest extends TestCase
{
    use BuildsGraphQLQueries;

    public function testQuery()
    {
        $query = $this->makeGraphQLQuery('reverse', ['str' => $this->makeVariable('str', 'String!'))], []);

        $response = $this->postGraphQL($query->getQuery(['str' => 'hello']));

        $this->assertEquals('olleh', $response->json('data.reverse.result'));

        $response = $this->postGraphQL($query->getQuery(['str' => 'world']));

        $this->assertEquals('dlrow', $response->json('data.reverse.result'));
    }
}
```

#### Using variables
The `MakesGraphQLQueries` trait introduces the `makeVariable(string $name, string $type): Variable` method, which introduces a variable to your query. The variable will automatically be declared in the root `query` or `mutation` as an argument when constructing the query:

```php
$query = $this->makeGraphQLQuery('hero', ['name' => $this->makeVariable('name', 'String!'), ['id']);
$query->getQuery(['name' => 'John Doe']);
```

```graphql
query($name: String!) {
    hero(name: $name) {
        id
    }
}
```

#### Uploading files
The `getQuery` method also supports instances of `Illuminate\Http\UploadedFile`, which means you can use Laravel's [fake uploaded files](https://laravel.com/docs/7.x/http-tests#testing-file-uploads) to test fields using the multipart file upload specification.

```php
$query->getQuery([
    'file' => UploadedFile::fake()->image('avatar.jpg')
]);
```

#### Using enum values
This library is able to automatically format numbers, strings, files and (associative) arrays. Unfortunately, without a custom type, it is not possible to recognize the difference between regular strings and enums. Thus, when using enums, use the `Enum` wrapper object. You can create one with the `makeEnum(string $name): Enum` method.

```php
$this->getMutation('setStatus', [
    'status' => $this->makeEnum('FINISHED')
]);
```

#### Using results
The query can be sent using Lighthouse's `postGraphQL` method, and thus you can refer to [Lighthouse's documentation on assertions](https://lighthouse-php.com/4.10/testing/phpunit.html#assertions) to use the queries to make assertions.

### Interpreting GraphQL query results
Laravel's default `TestResponse` is not really suited for asserting properties of GraphQL responses. The `InterpretsGraphQLResponses` trait aims to help with that.

```php
<?php
namespace Tests\Feature;

use Knevelina\LighthouseTest\Traits\InterpretsGraphQLResponses;
use Tests\TestCase;

class FeatureTest extends TestCase
{
    use InterpretsGraphQLResponses;
}
```

You may now use the following assertions:

```php
$response = $this->graphQL($query);

// Assert any error was returned.
$this->assertHasGraphQLError($response);
// Assert a certain error message was returned.
$this->assertHasGraphQLErrorMessage($response, 'Unauthenticated.');
// Assert no error has returned.
$this->assertHasNoGraphQLError($response);
```

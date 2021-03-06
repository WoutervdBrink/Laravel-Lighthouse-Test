<?php

namespace Knevelina\LighthouseTest\Traits;

use Knevelina\LighthouseTest\Schema\Enum;
use Knevelina\LighthouseTest\Schema\GraphQLQuery;
use Knevelina\LighthouseTest\Schema\Variable;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

/**
 * Adds some methods for constructing GraphQL queries for testing purposes.
 */
trait BuildsGraphQLQueries
{
    use MakesGraphQLRequests;

    /**
     * Use arguments as selection set if the selection set is empty.
     *
     * @param array $arguments
     * @param array $selection
     * @return array
     */
    private function resolveArgumentsAndSelection(array $arguments, array $selection = null): array
    {
        if ($selection === null) {
            return [[], $arguments];
        }
        
        return [$arguments, $selection];
    }

    /**
     * Make, but do not post a graph QL query. If <code>$selection</code> is
     * <code>null</code>, then <code>$arguments</code> is interpreted as the
     * selection.
     *
     * @param string $field
     * @param array $arguments
     * @param array $selection
     * @return GraphQLQuery
     */
    protected function makeGraphQLQuery(string $field, array $arguments = [], array $selection = null): GraphQLQuery
    {
        [$arguments, $selection] = $this->resolveArgumentsAndSelection($arguments, $selection);

        return new GraphQLQuery('query', $field, $arguments, $selection);
    }

    /**
     * Make, but do not post a graph QL mutation. If <code>$selection</code> is
     * <code>null</code>, then <code>$arguments</code> is interpreted as the
     * selection.
     *
     * @param string $field
     * @param array $arguments
     * @param array $selection
     * @return GraphQLQuery
     */
    protected function makeGraphQLMutation(string $field, array $arguments = [], array $selection = null): GraphQLQuery
    {
        [$arguments, $selection] = $this->resolveArgumentsAndSelection($arguments, $selection);

        return new GraphQLQuery('mutation', $field, $arguments, $selection);
    }

    /**
     * Make an enum for use in a constructed GraphQL query.
     *
     * @param string $value
     * @return Enum
     */
    protected function makeEnum(string $value): Enum
    {
        return new Enum($value);
    }

    protected function makeVariable(string $name, string $type): Variable
    {
        return new Variable($name, $type);
    }

    /**
     * Post a graph QL query. If <code>$selection</code> is <code>null</code>,
     * then <code>$arguments</code> is interpreted as the selection.
     *
     * @param string $field
     * @param array $arguments
     * @param array $selection
     * @return \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Testing\TestResponse
     */
    protected function postGraphQLQuery(string $field, array $arguments = [], array $selection = null)
    {
        $query = $this->makeGraphQLQuery($field, $arguments, $selection);

        return $this->postGraphQL($query->getQuery());
    }

    /**
     * Post a graph QL mutation. If <code>$selection</code> is
     * <code>null</code>, then <code>$arguments</code> is interpreted as the
     * selection.
     *
     * @param string $field
     * @param array $arguments
     * @param array $selection
     * @return \Illuminate\Foundation\Testing\TestResponse|\Illuminate\Testing\TestResponse
     */
    protected function postGraphQLMutation(string $field, array $arguments = [], array $selection = null)
    {
        $mutation = $this->makeGraphQLMutation($field, $arguments, $selection);

        return $this->postGraphQL($mutation->getQuery());
    }
}
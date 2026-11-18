import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\PointOfInterestController::index
* @see app/Http/Controllers/Api/PointOfInterestController.php:19
* @route '/api/points-of-interest'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/points-of-interest',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::index
* @see app/Http/Controllers/Api/PointOfInterestController.php:19
* @route '/api/points-of-interest'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::index
* @see app/Http/Controllers/Api/PointOfInterestController.php:19
* @route '/api/points-of-interest'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::index
* @see app/Http/Controllers/Api/PointOfInterestController.php:19
* @route '/api/points-of-interest'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::index
* @see app/Http/Controllers/Api/PointOfInterestController.php:19
* @route '/api/points-of-interest'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::index
* @see app/Http/Controllers/Api/PointOfInterestController.php:19
* @route '/api/points-of-interest'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::index
* @see app/Http/Controllers/Api/PointOfInterestController.php:19
* @route '/api/points-of-interest'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::show
* @see app/Http/Controllers/Api/PointOfInterestController.php:40
* @route '/api/points-of-interest/{pointOfInterest}'
*/
export const show = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/points-of-interest/{pointOfInterest}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::show
* @see app/Http/Controllers/Api/PointOfInterestController.php:40
* @route '/api/points-of-interest/{pointOfInterest}'
*/
show.url = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { pointOfInterest: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { pointOfInterest: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            pointOfInterest: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        pointOfInterest: typeof args.pointOfInterest === 'object'
        ? args.pointOfInterest.id
        : args.pointOfInterest,
    }

    return show.definition.url
            .replace('{pointOfInterest}', parsedArgs.pointOfInterest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::show
* @see app/Http/Controllers/Api/PointOfInterestController.php:40
* @route '/api/points-of-interest/{pointOfInterest}'
*/
show.get = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::show
* @see app/Http/Controllers/Api/PointOfInterestController.php:40
* @route '/api/points-of-interest/{pointOfInterest}'
*/
show.head = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::show
* @see app/Http/Controllers/Api/PointOfInterestController.php:40
* @route '/api/points-of-interest/{pointOfInterest}'
*/
const showForm = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::show
* @see app/Http/Controllers/Api/PointOfInterestController.php:40
* @route '/api/points-of-interest/{pointOfInterest}'
*/
showForm.get = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\PointOfInterestController::show
* @see app/Http/Controllers/Api/PointOfInterestController.php:40
* @route '/api/points-of-interest/{pointOfInterest}'
*/
showForm.head = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const PointOfInterestController = { index, show }

export default PointOfInterestController
import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\ExperienceController::index
* @see app/Http/Controllers/Api/ExperienceController.php:20
* @route '/api/experiences'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/experiences',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\ExperienceController::index
* @see app/Http/Controllers/Api/ExperienceController.php:20
* @route '/api/experiences'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ExperienceController::index
* @see app/Http/Controllers/Api/ExperienceController.php:20
* @route '/api/experiences'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ExperienceController::index
* @see app/Http/Controllers/Api/ExperienceController.php:20
* @route '/api/experiences'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\ExperienceController::index
* @see app/Http/Controllers/Api/ExperienceController.php:20
* @route '/api/experiences'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ExperienceController::index
* @see app/Http/Controllers/Api/ExperienceController.php:20
* @route '/api/experiences'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ExperienceController::index
* @see app/Http/Controllers/Api/ExperienceController.php:20
* @route '/api/experiences'
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
* @see \App\Http\Controllers\Api\ExperienceController::show
* @see app/Http/Controllers/Api/ExperienceController.php:44
* @route '/api/experiences/{experience}'
*/
export const show = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/experiences/{experience}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\ExperienceController::show
* @see app/Http/Controllers/Api/ExperienceController.php:44
* @route '/api/experiences/{experience}'
*/
show.url = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { experience: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { experience: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            experience: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        experience: typeof args.experience === 'object'
        ? args.experience.id
        : args.experience,
    }

    return show.definition.url
            .replace('{experience}', parsedArgs.experience.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ExperienceController::show
* @see app/Http/Controllers/Api/ExperienceController.php:44
* @route '/api/experiences/{experience}'
*/
show.get = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ExperienceController::show
* @see app/Http/Controllers/Api/ExperienceController.php:44
* @route '/api/experiences/{experience}'
*/
show.head = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\ExperienceController::show
* @see app/Http/Controllers/Api/ExperienceController.php:44
* @route '/api/experiences/{experience}'
*/
const showForm = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ExperienceController::show
* @see app/Http/Controllers/Api/ExperienceController.php:44
* @route '/api/experiences/{experience}'
*/
showForm.get = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ExperienceController::show
* @see app/Http/Controllers/Api/ExperienceController.php:44
* @route '/api/experiences/{experience}'
*/
showForm.head = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const ExperienceController = { index, show }

export default ExperienceController
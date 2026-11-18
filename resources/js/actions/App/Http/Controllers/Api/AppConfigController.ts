import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AppConfigController::__invoke
* @see app/Http/Controllers/Api/AppConfigController.php:16
* @route '/api/config'
*/
const AppConfigController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AppConfigController.url(options),
    method: 'get',
})

AppConfigController.definition = {
    methods: ["get","head"],
    url: '/api/config',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\AppConfigController::__invoke
* @see app/Http/Controllers/Api/AppConfigController.php:16
* @route '/api/config'
*/
AppConfigController.url = (options?: RouteQueryOptions) => {
    return AppConfigController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AppConfigController::__invoke
* @see app/Http/Controllers/Api/AppConfigController.php:16
* @route '/api/config'
*/
AppConfigController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: AppConfigController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\AppConfigController::__invoke
* @see app/Http/Controllers/Api/AppConfigController.php:16
* @route '/api/config'
*/
AppConfigController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: AppConfigController.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\AppConfigController::__invoke
* @see app/Http/Controllers/Api/AppConfigController.php:16
* @route '/api/config'
*/
const AppConfigControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: AppConfigController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\AppConfigController::__invoke
* @see app/Http/Controllers/Api/AppConfigController.php:16
* @route '/api/config'
*/
AppConfigControllerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: AppConfigController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\AppConfigController::__invoke
* @see app/Http/Controllers/Api/AppConfigController.php:16
* @route '/api/config'
*/
AppConfigControllerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: AppConfigController.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

AppConfigController.form = AppConfigControllerForm

export default AppConfigController
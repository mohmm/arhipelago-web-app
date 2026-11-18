import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\LegalPageController::terms
* @see app/Http/Controllers/LegalPageController.php:33
* @route '/terms'
*/
export const terms = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: terms.url(options),
    method: 'get',
})

terms.definition = {
    methods: ["get","head"],
    url: '/terms',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LegalPageController::terms
* @see app/Http/Controllers/LegalPageController.php:33
* @route '/terms'
*/
terms.url = (options?: RouteQueryOptions) => {
    return terms.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LegalPageController::terms
* @see app/Http/Controllers/LegalPageController.php:33
* @route '/terms'
*/
terms.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: terms.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::terms
* @see app/Http/Controllers/LegalPageController.php:33
* @route '/terms'
*/
terms.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: terms.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LegalPageController::terms
* @see app/Http/Controllers/LegalPageController.php:33
* @route '/terms'
*/
const termsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: terms.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::terms
* @see app/Http/Controllers/LegalPageController.php:33
* @route '/terms'
*/
termsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: terms.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::terms
* @see app/Http/Controllers/LegalPageController.php:33
* @route '/terms'
*/
termsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: terms.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

terms.form = termsForm

/**
* @see \App\Http\Controllers\LegalPageController::privacy
* @see app/Http/Controllers/LegalPageController.php:38
* @route '/privacy'
*/
export const privacy = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: privacy.url(options),
    method: 'get',
})

privacy.definition = {
    methods: ["get","head"],
    url: '/privacy',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LegalPageController::privacy
* @see app/Http/Controllers/LegalPageController.php:38
* @route '/privacy'
*/
privacy.url = (options?: RouteQueryOptions) => {
    return privacy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LegalPageController::privacy
* @see app/Http/Controllers/LegalPageController.php:38
* @route '/privacy'
*/
privacy.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: privacy.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::privacy
* @see app/Http/Controllers/LegalPageController.php:38
* @route '/privacy'
*/
privacy.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: privacy.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LegalPageController::privacy
* @see app/Http/Controllers/LegalPageController.php:38
* @route '/privacy'
*/
const privacyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: privacy.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::privacy
* @see app/Http/Controllers/LegalPageController.php:38
* @route '/privacy'
*/
privacyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: privacy.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::privacy
* @see app/Http/Controllers/LegalPageController.php:38
* @route '/privacy'
*/
privacyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: privacy.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

privacy.form = privacyForm

/**
* @see \App\Http\Controllers\LegalPageController::cookies
* @see app/Http/Controllers/LegalPageController.php:43
* @route '/cookies'
*/
export const cookies = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cookies.url(options),
    method: 'get',
})

cookies.definition = {
    methods: ["get","head"],
    url: '/cookies',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LegalPageController::cookies
* @see app/Http/Controllers/LegalPageController.php:43
* @route '/cookies'
*/
cookies.url = (options?: RouteQueryOptions) => {
    return cookies.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LegalPageController::cookies
* @see app/Http/Controllers/LegalPageController.php:43
* @route '/cookies'
*/
cookies.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cookies.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::cookies
* @see app/Http/Controllers/LegalPageController.php:43
* @route '/cookies'
*/
cookies.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: cookies.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LegalPageController::cookies
* @see app/Http/Controllers/LegalPageController.php:43
* @route '/cookies'
*/
const cookiesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: cookies.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::cookies
* @see app/Http/Controllers/LegalPageController.php:43
* @route '/cookies'
*/
cookiesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: cookies.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::cookies
* @see app/Http/Controllers/LegalPageController.php:43
* @route '/cookies'
*/
cookiesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: cookies.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

cookies.form = cookiesForm

/**
* @see \App\Http\Controllers\LegalPageController::ai
* @see app/Http/Controllers/LegalPageController.php:48
* @route '/ai'
*/
export const ai = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ai.url(options),
    method: 'get',
})

ai.definition = {
    methods: ["get","head"],
    url: '/ai',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LegalPageController::ai
* @see app/Http/Controllers/LegalPageController.php:48
* @route '/ai'
*/
ai.url = (options?: RouteQueryOptions) => {
    return ai.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LegalPageController::ai
* @see app/Http/Controllers/LegalPageController.php:48
* @route '/ai'
*/
ai.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ai.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::ai
* @see app/Http/Controllers/LegalPageController.php:48
* @route '/ai'
*/
ai.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ai.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LegalPageController::ai
* @see app/Http/Controllers/LegalPageController.php:48
* @route '/ai'
*/
const aiForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ai.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::ai
* @see app/Http/Controllers/LegalPageController.php:48
* @route '/ai'
*/
aiForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ai.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LegalPageController::ai
* @see app/Http/Controllers/LegalPageController.php:48
* @route '/ai'
*/
aiForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ai.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ai.form = aiForm

const LegalPageController = { terms, privacy, cookies, ai }

export default LegalPageController
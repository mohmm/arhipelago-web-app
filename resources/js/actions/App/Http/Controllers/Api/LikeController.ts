import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\LikeController::pointOfInterest
* @see app/Http/Controllers/Api/LikeController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/like'
*/
export const pointOfInterest = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: pointOfInterest.url(args, options),
    method: 'post',
})

pointOfInterest.definition = {
    methods: ["post"],
    url: '/api/points-of-interest/{pointOfInterest}/like',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\LikeController::pointOfInterest
* @see app/Http/Controllers/Api/LikeController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/like'
*/
pointOfInterest.url = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return pointOfInterest.definition.url
            .replace('{pointOfInterest}', parsedArgs.pointOfInterest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\LikeController::pointOfInterest
* @see app/Http/Controllers/Api/LikeController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/like'
*/
pointOfInterest.post = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: pointOfInterest.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\LikeController::pointOfInterest
* @see app/Http/Controllers/Api/LikeController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/like'
*/
const pointOfInterestForm = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: pointOfInterest.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\LikeController::pointOfInterest
* @see app/Http/Controllers/Api/LikeController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/like'
*/
pointOfInterestForm.post = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: pointOfInterest.url(args, options),
    method: 'post',
})

pointOfInterest.form = pointOfInterestForm

/**
* @see \App\Http\Controllers\Api\LikeController::post
* @see app/Http/Controllers/Api/LikeController.php:16
* @route '/api/posts/{post}/like'
*/
export const post = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: post.url(args, options),
    method: 'post',
})

post.definition = {
    methods: ["post"],
    url: '/api/posts/{post}/like',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\LikeController::post
* @see app/Http/Controllers/Api/LikeController.php:16
* @route '/api/posts/{post}/like'
*/
post.url = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { post: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { post: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            post: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        post: typeof args.post === 'object'
        ? args.post.id
        : args.post,
    }

    return post.definition.url
            .replace('{post}', parsedArgs.post.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\LikeController::post
* @see app/Http/Controllers/Api/LikeController.php:16
* @route '/api/posts/{post}/like'
*/
post.post = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: post.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\LikeController::post
* @see app/Http/Controllers/Api/LikeController.php:16
* @route '/api/posts/{post}/like'
*/
const postForm = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: post.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\LikeController::post
* @see app/Http/Controllers/Api/LikeController.php:16
* @route '/api/posts/{post}/like'
*/
postForm.post = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: post.url(args, options),
    method: 'post',
})

post.form = postForm

const LikeController = { pointOfInterest, post }

export default LikeController
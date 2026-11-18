import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\ReviewController::forPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
export const forPointOfInterest = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: forPointOfInterest.url(args, options),
    method: 'get',
})

forPointOfInterest.definition = {
    methods: ["get","head"],
    url: '/api/points-of-interest/{pointOfInterest}/reviews',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\ReviewController::forPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
forPointOfInterest.url = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return forPointOfInterest.definition.url
            .replace('{pointOfInterest}', parsedArgs.pointOfInterest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ReviewController::forPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
forPointOfInterest.get = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: forPointOfInterest.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::forPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
forPointOfInterest.head = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: forPointOfInterest.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::forPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
const forPointOfInterestForm = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: forPointOfInterest.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::forPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
forPointOfInterestForm.get = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: forPointOfInterest.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::forPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:23
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
forPointOfInterestForm.head = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: forPointOfInterest.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

forPointOfInterest.form = forPointOfInterestForm

/**
* @see \App\Http\Controllers\Api\ReviewController::forExperience
* @see app/Http/Controllers/Api/ReviewController.php:37
* @route '/api/experiences/{experience}/reviews'
*/
export const forExperience = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: forExperience.url(args, options),
    method: 'get',
})

forExperience.definition = {
    methods: ["get","head"],
    url: '/api/experiences/{experience}/reviews',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\ReviewController::forExperience
* @see app/Http/Controllers/Api/ReviewController.php:37
* @route '/api/experiences/{experience}/reviews'
*/
forExperience.url = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return forExperience.definition.url
            .replace('{experience}', parsedArgs.experience.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ReviewController::forExperience
* @see app/Http/Controllers/Api/ReviewController.php:37
* @route '/api/experiences/{experience}/reviews'
*/
forExperience.get = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: forExperience.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::forExperience
* @see app/Http/Controllers/Api/ReviewController.php:37
* @route '/api/experiences/{experience}/reviews'
*/
forExperience.head = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: forExperience.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::forExperience
* @see app/Http/Controllers/Api/ReviewController.php:37
* @route '/api/experiences/{experience}/reviews'
*/
const forExperienceForm = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: forExperience.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::forExperience
* @see app/Http/Controllers/Api/ReviewController.php:37
* @route '/api/experiences/{experience}/reviews'
*/
forExperienceForm.get = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: forExperience.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::forExperience
* @see app/Http/Controllers/Api/ReviewController.php:37
* @route '/api/experiences/{experience}/reviews'
*/
forExperienceForm.head = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: forExperience.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

forExperience.form = forExperienceForm

/**
* @see \App\Http\Controllers\Api\ReviewController::storeForPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:30
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
export const storeForPointOfInterest = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeForPointOfInterest.url(args, options),
    method: 'post',
})

storeForPointOfInterest.definition = {
    methods: ["post"],
    url: '/api/points-of-interest/{pointOfInterest}/reviews',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\ReviewController::storeForPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:30
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
storeForPointOfInterest.url = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return storeForPointOfInterest.definition.url
            .replace('{pointOfInterest}', parsedArgs.pointOfInterest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ReviewController::storeForPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:30
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
storeForPointOfInterest.post = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeForPointOfInterest.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::storeForPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:30
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
const storeForPointOfInterestForm = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeForPointOfInterest.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::storeForPointOfInterest
* @see app/Http/Controllers/Api/ReviewController.php:30
* @route '/api/points-of-interest/{pointOfInterest}/reviews'
*/
storeForPointOfInterestForm.post = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeForPointOfInterest.url(args, options),
    method: 'post',
})

storeForPointOfInterest.form = storeForPointOfInterestForm

/**
* @see \App\Http\Controllers\Api\ReviewController::storeForExperience
* @see app/Http/Controllers/Api/ReviewController.php:44
* @route '/api/experiences/{experience}/reviews'
*/
export const storeForExperience = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeForExperience.url(args, options),
    method: 'post',
})

storeForExperience.definition = {
    methods: ["post"],
    url: '/api/experiences/{experience}/reviews',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\ReviewController::storeForExperience
* @see app/Http/Controllers/Api/ReviewController.php:44
* @route '/api/experiences/{experience}/reviews'
*/
storeForExperience.url = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return storeForExperience.definition.url
            .replace('{experience}', parsedArgs.experience.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ReviewController::storeForExperience
* @see app/Http/Controllers/Api/ReviewController.php:44
* @route '/api/experiences/{experience}/reviews'
*/
storeForExperience.post = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeForExperience.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::storeForExperience
* @see app/Http/Controllers/Api/ReviewController.php:44
* @route '/api/experiences/{experience}/reviews'
*/
const storeForExperienceForm = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeForExperience.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::storeForExperience
* @see app/Http/Controllers/Api/ReviewController.php:44
* @route '/api/experiences/{experience}/reviews'
*/
storeForExperienceForm.post = (args: { experience: number | { id: number } } | [experience: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeForExperience.url(args, options),
    method: 'post',
})

storeForExperience.form = storeForExperienceForm

/**
* @see \App\Http\Controllers\Api\ReviewController::destroy
* @see app/Http/Controllers/Api/ReviewController.php:51
* @route '/api/reviews/{review}'
*/
export const destroy = (args: { review: number | { id: number } } | [review: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/reviews/{review}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\ReviewController::destroy
* @see app/Http/Controllers/Api/ReviewController.php:51
* @route '/api/reviews/{review}'
*/
destroy.url = (args: { review: number | { id: number } } | [review: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { review: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { review: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            review: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        review: typeof args.review === 'object'
        ? args.review.id
        : args.review,
    }

    return destroy.definition.url
            .replace('{review}', parsedArgs.review.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ReviewController::destroy
* @see app/Http/Controllers/Api/ReviewController.php:51
* @route '/api/reviews/{review}'
*/
destroy.delete = (args: { review: number | { id: number } } | [review: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::destroy
* @see app/Http/Controllers/Api/ReviewController.php:51
* @route '/api/reviews/{review}'
*/
const destroyForm = (args: { review: number | { id: number } } | [review: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\ReviewController::destroy
* @see app/Http/Controllers/Api/ReviewController.php:51
* @route '/api/reviews/{review}'
*/
destroyForm.delete = (args: { review: number | { id: number } } | [review: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const ReviewController = { forPointOfInterest, forExperience, storeForPointOfInterest, storeForExperience, destroy }

export default ReviewController
import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::store
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:18
* @route '/admin/points-of-interest/{pointOfInterest}/photos'
*/
export const store = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/points-of-interest/{pointOfInterest}/photos',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::store
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:18
* @route '/admin/points-of-interest/{pointOfInterest}/photos'
*/
store.url = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return store.definition.url
            .replace('{pointOfInterest}', parsedArgs.pointOfInterest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::store
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:18
* @route '/admin/points-of-interest/{pointOfInterest}/photos'
*/
store.post = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::store
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:18
* @route '/admin/points-of-interest/{pointOfInterest}/photos'
*/
const storeForm = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::store
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:18
* @route '/admin/points-of-interest/{pointOfInterest}/photos'
*/
storeForm.post = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::cover
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:32
* @route '/admin/points-of-interest/{pointOfInterest}/photos/{media}/cover'
*/
export const cover = (args: { pointOfInterest: number | { id: number }, media: number | { id: number } } | [pointOfInterest: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cover.url(args, options),
    method: 'post',
})

cover.definition = {
    methods: ["post"],
    url: '/admin/points-of-interest/{pointOfInterest}/photos/{media}/cover',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::cover
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:32
* @route '/admin/points-of-interest/{pointOfInterest}/photos/{media}/cover'
*/
cover.url = (args: { pointOfInterest: number | { id: number }, media: number | { id: number } } | [pointOfInterest: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            pointOfInterest: args[0],
            media: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        pointOfInterest: typeof args.pointOfInterest === 'object'
        ? args.pointOfInterest.id
        : args.pointOfInterest,
        media: typeof args.media === 'object'
        ? args.media.id
        : args.media,
    }

    return cover.definition.url
            .replace('{pointOfInterest}', parsedArgs.pointOfInterest.toString())
            .replace('{media}', parsedArgs.media.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::cover
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:32
* @route '/admin/points-of-interest/{pointOfInterest}/photos/{media}/cover'
*/
cover.post = (args: { pointOfInterest: number | { id: number }, media: number | { id: number } } | [pointOfInterest: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cover.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::cover
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:32
* @route '/admin/points-of-interest/{pointOfInterest}/photos/{media}/cover'
*/
const coverForm = (args: { pointOfInterest: number | { id: number }, media: number | { id: number } } | [pointOfInterest: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cover.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::cover
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:32
* @route '/admin/points-of-interest/{pointOfInterest}/photos/{media}/cover'
*/
coverForm.post = (args: { pointOfInterest: number | { id: number }, media: number | { id: number } } | [pointOfInterest: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cover.url(args, options),
    method: 'post',
})

cover.form = coverForm

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::destroy
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:46
* @route '/admin/points-of-interest/{pointOfInterest}/photos/{media}'
*/
export const destroy = (args: { pointOfInterest: number | { id: number }, media: number | { id: number } } | [pointOfInterest: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/points-of-interest/{pointOfInterest}/photos/{media}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::destroy
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:46
* @route '/admin/points-of-interest/{pointOfInterest}/photos/{media}'
*/
destroy.url = (args: { pointOfInterest: number | { id: number }, media: number | { id: number } } | [pointOfInterest: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            pointOfInterest: args[0],
            media: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        pointOfInterest: typeof args.pointOfInterest === 'object'
        ? args.pointOfInterest.id
        : args.pointOfInterest,
        media: typeof args.media === 'object'
        ? args.media.id
        : args.media,
    }

    return destroy.definition.url
            .replace('{pointOfInterest}', parsedArgs.pointOfInterest.toString())
            .replace('{media}', parsedArgs.media.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::destroy
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:46
* @route '/admin/points-of-interest/{pointOfInterest}/photos/{media}'
*/
destroy.delete = (args: { pointOfInterest: number | { id: number }, media: number | { id: number } } | [pointOfInterest: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::destroy
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:46
* @route '/admin/points-of-interest/{pointOfInterest}/photos/{media}'
*/
const destroyForm = (args: { pointOfInterest: number | { id: number }, media: number | { id: number } } | [pointOfInterest: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestPhotoController::destroy
* @see app/Http/Controllers/Admin/PointOfInterestPhotoController.php:46
* @route '/admin/points-of-interest/{pointOfInterest}/photos/{media}'
*/
destroyForm.delete = (args: { pointOfInterest: number | { id: number }, media: number | { id: number } } | [pointOfInterest: number | { id: number }, media: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const PointOfInterestPhotoController = { store, cover, destroy }

export default PointOfInterestPhotoController
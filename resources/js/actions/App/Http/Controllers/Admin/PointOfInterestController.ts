import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::index
* @see app/Http/Controllers/Admin/PointOfInterestController.php:26
* @route '/admin/points-of-interest'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/points-of-interest',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::index
* @see app/Http/Controllers/Admin/PointOfInterestController.php:26
* @route '/admin/points-of-interest'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::index
* @see app/Http/Controllers/Admin/PointOfInterestController.php:26
* @route '/admin/points-of-interest'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::index
* @see app/Http/Controllers/Admin/PointOfInterestController.php:26
* @route '/admin/points-of-interest'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::index
* @see app/Http/Controllers/Admin/PointOfInterestController.php:26
* @route '/admin/points-of-interest'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::index
* @see app/Http/Controllers/Admin/PointOfInterestController.php:26
* @route '/admin/points-of-interest'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::index
* @see app/Http/Controllers/Admin/PointOfInterestController.php:26
* @route '/admin/points-of-interest'
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
* @see \App\Http\Controllers\Admin\PointOfInterestController::create
* @see app/Http/Controllers/Admin/PointOfInterestController.php:65
* @route '/admin/points-of-interest/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/points-of-interest/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::create
* @see app/Http/Controllers/Admin/PointOfInterestController.php:65
* @route '/admin/points-of-interest/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::create
* @see app/Http/Controllers/Admin/PointOfInterestController.php:65
* @route '/admin/points-of-interest/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::create
* @see app/Http/Controllers/Admin/PointOfInterestController.php:65
* @route '/admin/points-of-interest/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::create
* @see app/Http/Controllers/Admin/PointOfInterestController.php:65
* @route '/admin/points-of-interest/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::create
* @see app/Http/Controllers/Admin/PointOfInterestController.php:65
* @route '/admin/points-of-interest/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::create
* @see app/Http/Controllers/Admin/PointOfInterestController.php:65
* @route '/admin/points-of-interest/create'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::store
* @see app/Http/Controllers/Admin/PointOfInterestController.php:75
* @route '/admin/points-of-interest'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/points-of-interest',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::store
* @see app/Http/Controllers/Admin/PointOfInterestController.php:75
* @route '/admin/points-of-interest'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::store
* @see app/Http/Controllers/Admin/PointOfInterestController.php:75
* @route '/admin/points-of-interest'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::store
* @see app/Http/Controllers/Admin/PointOfInterestController.php:75
* @route '/admin/points-of-interest'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::store
* @see app/Http/Controllers/Admin/PointOfInterestController.php:75
* @route '/admin/points-of-interest'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::edit
* @see app/Http/Controllers/Admin/PointOfInterestController.php:91
* @route '/admin/points-of-interest/{pointOfInterest}/edit'
*/
export const edit = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/points-of-interest/{pointOfInterest}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::edit
* @see app/Http/Controllers/Admin/PointOfInterestController.php:91
* @route '/admin/points-of-interest/{pointOfInterest}/edit'
*/
edit.url = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return edit.definition.url
            .replace('{pointOfInterest}', parsedArgs.pointOfInterest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::edit
* @see app/Http/Controllers/Admin/PointOfInterestController.php:91
* @route '/admin/points-of-interest/{pointOfInterest}/edit'
*/
edit.get = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::edit
* @see app/Http/Controllers/Admin/PointOfInterestController.php:91
* @route '/admin/points-of-interest/{pointOfInterest}/edit'
*/
edit.head = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::edit
* @see app/Http/Controllers/Admin/PointOfInterestController.php:91
* @route '/admin/points-of-interest/{pointOfInterest}/edit'
*/
const editForm = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::edit
* @see app/Http/Controllers/Admin/PointOfInterestController.php:91
* @route '/admin/points-of-interest/{pointOfInterest}/edit'
*/
editForm.get = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::edit
* @see app/Http/Controllers/Admin/PointOfInterestController.php:91
* @route '/admin/points-of-interest/{pointOfInterest}/edit'
*/
editForm.head = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::update
* @see app/Http/Controllers/Admin/PointOfInterestController.php:124
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
export const update = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/points-of-interest/{pointOfInterest}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::update
* @see app/Http/Controllers/Admin/PointOfInterestController.php:124
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
update.url = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{pointOfInterest}', parsedArgs.pointOfInterest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::update
* @see app/Http/Controllers/Admin/PointOfInterestController.php:124
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
update.put = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::update
* @see app/Http/Controllers/Admin/PointOfInterestController.php:124
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
update.patch = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::update
* @see app/Http/Controllers/Admin/PointOfInterestController.php:124
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
const updateForm = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::update
* @see app/Http/Controllers/Admin/PointOfInterestController.php:124
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
updateForm.put = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::update
* @see app/Http/Controllers/Admin/PointOfInterestController.php:124
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
updateForm.patch = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::destroy
* @see app/Http/Controllers/Admin/PointOfInterestController.php:138
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
export const destroy = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/points-of-interest/{pointOfInterest}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::destroy
* @see app/Http/Controllers/Admin/PointOfInterestController.php:138
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
destroy.url = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{pointOfInterest}', parsedArgs.pointOfInterest.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::destroy
* @see app/Http/Controllers/Admin/PointOfInterestController.php:138
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
destroy.delete = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::destroy
* @see app/Http/Controllers/Admin/PointOfInterestController.php:138
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
const destroyForm = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\PointOfInterestController::destroy
* @see app/Http/Controllers/Admin/PointOfInterestController.php:138
* @route '/admin/points-of-interest/{pointOfInterest}'
*/
destroyForm.delete = (args: { pointOfInterest: number | { id: number } } | [pointOfInterest: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const PointOfInterestController = { index, create, store, edit, update, destroy }

export default PointOfInterestController
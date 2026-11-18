import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DestinationController::index
* @see app/Http/Controllers/Admin/DestinationController.php:18
* @route '/admin/destinations'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/destinations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DestinationController::index
* @see app/Http/Controllers/Admin/DestinationController.php:18
* @route '/admin/destinations'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DestinationController::index
* @see app/Http/Controllers/Admin/DestinationController.php:18
* @route '/admin/destinations'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::index
* @see app/Http/Controllers/Admin/DestinationController.php:18
* @route '/admin/destinations'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::index
* @see app/Http/Controllers/Admin/DestinationController.php:18
* @route '/admin/destinations'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::index
* @see app/Http/Controllers/Admin/DestinationController.php:18
* @route '/admin/destinations'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::index
* @see app/Http/Controllers/Admin/DestinationController.php:18
* @route '/admin/destinations'
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
* @see \App\Http\Controllers\Admin\DestinationController::create
* @see app/Http/Controllers/Admin/DestinationController.php:34
* @route '/admin/destinations/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/destinations/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DestinationController::create
* @see app/Http/Controllers/Admin/DestinationController.php:34
* @route '/admin/destinations/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DestinationController::create
* @see app/Http/Controllers/Admin/DestinationController.php:34
* @route '/admin/destinations/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::create
* @see app/Http/Controllers/Admin/DestinationController.php:34
* @route '/admin/destinations/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::create
* @see app/Http/Controllers/Admin/DestinationController.php:34
* @route '/admin/destinations/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::create
* @see app/Http/Controllers/Admin/DestinationController.php:34
* @route '/admin/destinations/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::create
* @see app/Http/Controllers/Admin/DestinationController.php:34
* @route '/admin/destinations/create'
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
* @see \App\Http\Controllers\Admin\DestinationController::store
* @see app/Http/Controllers/Admin/DestinationController.php:41
* @route '/admin/destinations'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/destinations',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DestinationController::store
* @see app/Http/Controllers/Admin/DestinationController.php:41
* @route '/admin/destinations'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DestinationController::store
* @see app/Http/Controllers/Admin/DestinationController.php:41
* @route '/admin/destinations'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::store
* @see app/Http/Controllers/Admin/DestinationController.php:41
* @route '/admin/destinations'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::store
* @see app/Http/Controllers/Admin/DestinationController.php:41
* @route '/admin/destinations'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Admin\DestinationController::edit
* @see app/Http/Controllers/Admin/DestinationController.php:52
* @route '/admin/destinations/{destination}/edit'
*/
export const edit = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/destinations/{destination}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DestinationController::edit
* @see app/Http/Controllers/Admin/DestinationController.php:52
* @route '/admin/destinations/{destination}/edit'
*/
edit.url = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { destination: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { destination: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            destination: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        destination: typeof args.destination === 'object'
        ? args.destination.id
        : args.destination,
    }

    return edit.definition.url
            .replace('{destination}', parsedArgs.destination.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DestinationController::edit
* @see app/Http/Controllers/Admin/DestinationController.php:52
* @route '/admin/destinations/{destination}/edit'
*/
edit.get = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::edit
* @see app/Http/Controllers/Admin/DestinationController.php:52
* @route '/admin/destinations/{destination}/edit'
*/
edit.head = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::edit
* @see app/Http/Controllers/Admin/DestinationController.php:52
* @route '/admin/destinations/{destination}/edit'
*/
const editForm = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::edit
* @see app/Http/Controllers/Admin/DestinationController.php:52
* @route '/admin/destinations/{destination}/edit'
*/
editForm.get = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::edit
* @see app/Http/Controllers/Admin/DestinationController.php:52
* @route '/admin/destinations/{destination}/edit'
*/
editForm.head = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\DestinationController::update
* @see app/Http/Controllers/Admin/DestinationController.php:61
* @route '/admin/destinations/{destination}'
*/
export const update = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/destinations/{destination}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\DestinationController::update
* @see app/Http/Controllers/Admin/DestinationController.php:61
* @route '/admin/destinations/{destination}'
*/
update.url = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { destination: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { destination: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            destination: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        destination: typeof args.destination === 'object'
        ? args.destination.id
        : args.destination,
    }

    return update.definition.url
            .replace('{destination}', parsedArgs.destination.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DestinationController::update
* @see app/Http/Controllers/Admin/DestinationController.php:61
* @route '/admin/destinations/{destination}'
*/
update.put = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::update
* @see app/Http/Controllers/Admin/DestinationController.php:61
* @route '/admin/destinations/{destination}'
*/
update.patch = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::update
* @see app/Http/Controllers/Admin/DestinationController.php:61
* @route '/admin/destinations/{destination}'
*/
const updateForm = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::update
* @see app/Http/Controllers/Admin/DestinationController.php:61
* @route '/admin/destinations/{destination}'
*/
updateForm.put = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::update
* @see app/Http/Controllers/Admin/DestinationController.php:61
* @route '/admin/destinations/{destination}'
*/
updateForm.patch = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\DestinationController::destroy
* @see app/Http/Controllers/Admin/DestinationController.php:72
* @route '/admin/destinations/{destination}'
*/
export const destroy = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/destinations/{destination}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\DestinationController::destroy
* @see app/Http/Controllers/Admin/DestinationController.php:72
* @route '/admin/destinations/{destination}'
*/
destroy.url = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { destination: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { destination: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            destination: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        destination: typeof args.destination === 'object'
        ? args.destination.id
        : args.destination,
    }

    return destroy.definition.url
            .replace('{destination}', parsedArgs.destination.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DestinationController::destroy
* @see app/Http/Controllers/Admin/DestinationController.php:72
* @route '/admin/destinations/{destination}'
*/
destroy.delete = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::destroy
* @see app/Http/Controllers/Admin/DestinationController.php:72
* @route '/admin/destinations/{destination}'
*/
const destroyForm = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\DestinationController::destroy
* @see app/Http/Controllers/Admin/DestinationController.php:72
* @route '/admin/destinations/{destination}'
*/
destroyForm.delete = (args: { destination: number | { id: number } } | [destination: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const destinations = {
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default destinations
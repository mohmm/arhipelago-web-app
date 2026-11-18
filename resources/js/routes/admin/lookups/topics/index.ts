import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::index
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:19
* @route '/admin/lookups/topics'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/lookups/topics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::index
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:19
* @route '/admin/lookups/topics'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::index
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:19
* @route '/admin/lookups/topics'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::index
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:19
* @route '/admin/lookups/topics'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::index
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:19
* @route '/admin/lookups/topics'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::index
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:19
* @route '/admin/lookups/topics'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::index
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:19
* @route '/admin/lookups/topics'
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
* @see \App\Http\Controllers\Admin\Lookups\TopicController::create
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:37
* @route '/admin/lookups/topics/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/lookups/topics/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::create
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:37
* @route '/admin/lookups/topics/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::create
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:37
* @route '/admin/lookups/topics/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::create
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:37
* @route '/admin/lookups/topics/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::create
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:37
* @route '/admin/lookups/topics/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::create
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:37
* @route '/admin/lookups/topics/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::create
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:37
* @route '/admin/lookups/topics/create'
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
* @see \App\Http\Controllers\Admin\Lookups\TopicController::store
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:47
* @route '/admin/lookups/topics'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/lookups/topics',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::store
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:47
* @route '/admin/lookups/topics'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::store
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:47
* @route '/admin/lookups/topics'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::store
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:47
* @route '/admin/lookups/topics'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::store
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:47
* @route '/admin/lookups/topics'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::show
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:61
* @route '/admin/lookups/topics/{topic}'
*/
export const show = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/lookups/topics/{topic}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::show
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:61
* @route '/admin/lookups/topics/{topic}'
*/
show.url = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { topic: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { topic: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            topic: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        topic: typeof args.topic === 'object'
        ? args.topic.id
        : args.topic,
    }

    return show.definition.url
            .replace('{topic}', parsedArgs.topic.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::show
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:61
* @route '/admin/lookups/topics/{topic}'
*/
show.get = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::show
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:61
* @route '/admin/lookups/topics/{topic}'
*/
show.head = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::show
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:61
* @route '/admin/lookups/topics/{topic}'
*/
const showForm = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::show
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:61
* @route '/admin/lookups/topics/{topic}'
*/
showForm.get = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::show
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:61
* @route '/admin/lookups/topics/{topic}'
*/
showForm.head = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::edit
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:73
* @route '/admin/lookups/topics/{topic}/edit'
*/
export const edit = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/lookups/topics/{topic}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::edit
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:73
* @route '/admin/lookups/topics/{topic}/edit'
*/
edit.url = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { topic: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { topic: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            topic: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        topic: typeof args.topic === 'object'
        ? args.topic.id
        : args.topic,
    }

    return edit.definition.url
            .replace('{topic}', parsedArgs.topic.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::edit
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:73
* @route '/admin/lookups/topics/{topic}/edit'
*/
edit.get = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::edit
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:73
* @route '/admin/lookups/topics/{topic}/edit'
*/
edit.head = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::edit
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:73
* @route '/admin/lookups/topics/{topic}/edit'
*/
const editForm = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::edit
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:73
* @route '/admin/lookups/topics/{topic}/edit'
*/
editForm.get = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::edit
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:73
* @route '/admin/lookups/topics/{topic}/edit'
*/
editForm.head = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\Lookups\TopicController::update
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:85
* @route '/admin/lookups/topics/{topic}'
*/
export const update = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/lookups/topics/{topic}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::update
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:85
* @route '/admin/lookups/topics/{topic}'
*/
update.url = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { topic: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { topic: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            topic: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        topic: typeof args.topic === 'object'
        ? args.topic.id
        : args.topic,
    }

    return update.definition.url
            .replace('{topic}', parsedArgs.topic.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::update
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:85
* @route '/admin/lookups/topics/{topic}'
*/
update.put = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::update
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:85
* @route '/admin/lookups/topics/{topic}'
*/
update.patch = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::update
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:85
* @route '/admin/lookups/topics/{topic}'
*/
const updateForm = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::update
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:85
* @route '/admin/lookups/topics/{topic}'
*/
updateForm.put = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::update
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:85
* @route '/admin/lookups/topics/{topic}'
*/
updateForm.patch = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\Lookups\TopicController::destroy
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:99
* @route '/admin/lookups/topics/{topic}'
*/
export const destroy = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/lookups/topics/{topic}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::destroy
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:99
* @route '/admin/lookups/topics/{topic}'
*/
destroy.url = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { topic: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { topic: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            topic: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        topic: typeof args.topic === 'object'
        ? args.topic.id
        : args.topic,
    }

    return destroy.definition.url
            .replace('{topic}', parsedArgs.topic.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::destroy
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:99
* @route '/admin/lookups/topics/{topic}'
*/
destroy.delete = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::destroy
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:99
* @route '/admin/lookups/topics/{topic}'
*/
const destroyForm = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\Lookups\TopicController::destroy
* @see app/Http/Controllers/Admin/Lookups/TopicController.php:99
* @route '/admin/lookups/topics/{topic}'
*/
destroyForm.delete = (args: { topic: number | { id: number } } | [topic: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const topics = {
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default topics
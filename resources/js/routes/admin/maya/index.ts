import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::edit
* @see app/Http/Controllers/Admin/MayaSettingsController.php:19
* @route '/admin/maya'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/maya',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::edit
* @see app/Http/Controllers/Admin/MayaSettingsController.php:19
* @route '/admin/maya'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::edit
* @see app/Http/Controllers/Admin/MayaSettingsController.php:19
* @route '/admin/maya'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::edit
* @see app/Http/Controllers/Admin/MayaSettingsController.php:19
* @route '/admin/maya'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::edit
* @see app/Http/Controllers/Admin/MayaSettingsController.php:19
* @route '/admin/maya'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::edit
* @see app/Http/Controllers/Admin/MayaSettingsController.php:19
* @route '/admin/maya'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::edit
* @see app/Http/Controllers/Admin/MayaSettingsController.php:19
* @route '/admin/maya'
*/
editForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::update
* @see app/Http/Controllers/Admin/MayaSettingsController.php:27
* @route '/admin/maya'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/admin/maya',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::update
* @see app/Http/Controllers/Admin/MayaSettingsController.php:27
* @route '/admin/maya'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::update
* @see app/Http/Controllers/Admin/MayaSettingsController.php:27
* @route '/admin/maya'
*/
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::update
* @see app/Http/Controllers/Admin/MayaSettingsController.php:27
* @route '/admin/maya'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\MayaSettingsController::update
* @see app/Http/Controllers/Admin/MayaSettingsController.php:27
* @route '/admin/maya'
*/
updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const maya = {
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
}

export default maya
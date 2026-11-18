import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\MeController::show
* @see app/Http/Controllers/Api/MeController.php:33
* @route '/api/me'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/me',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\MeController::show
* @see app/Http/Controllers/Api/MeController.php:33
* @route '/api/me'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\MeController::show
* @see app/Http/Controllers/Api/MeController.php:33
* @route '/api/me'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\MeController::show
* @see app/Http/Controllers/Api/MeController.php:33
* @route '/api/me'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\MeController::show
* @see app/Http/Controllers/Api/MeController.php:33
* @route '/api/me'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\MeController::show
* @see app/Http/Controllers/Api/MeController.php:33
* @route '/api/me'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\MeController::show
* @see app/Http/Controllers/Api/MeController.php:33
* @route '/api/me'
*/
showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\Api\MeController::update
* @see app/Http/Controllers/Api/MeController.php:38
* @route '/api/me'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/me',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Api\MeController::update
* @see app/Http/Controllers/Api/MeController.php:38
* @route '/api/me'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\MeController::update
* @see app/Http/Controllers/Api/MeController.php:38
* @route '/api/me'
*/
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Api\MeController::update
* @see app/Http/Controllers/Api/MeController.php:38
* @route '/api/me'
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
* @see \App\Http\Controllers\Api\MeController::update
* @see app/Http/Controllers/Api/MeController.php:38
* @route '/api/me'
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

/**
* @see \App\Http\Controllers\Api\MeController::updatePassword
* @see app/Http/Controllers/Api/MeController.php:46
* @route '/api/me/password'
*/
export const updatePassword = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatePassword.url(options),
    method: 'put',
})

updatePassword.definition = {
    methods: ["put"],
    url: '/api/me/password',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Api\MeController::updatePassword
* @see app/Http/Controllers/Api/MeController.php:46
* @route '/api/me/password'
*/
updatePassword.url = (options?: RouteQueryOptions) => {
    return updatePassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\MeController::updatePassword
* @see app/Http/Controllers/Api/MeController.php:46
* @route '/api/me/password'
*/
updatePassword.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatePassword.url(options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Api\MeController::updatePassword
* @see app/Http/Controllers/Api/MeController.php:46
* @route '/api/me/password'
*/
const updatePasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePassword.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\MeController::updatePassword
* @see app/Http/Controllers/Api/MeController.php:46
* @route '/api/me/password'
*/
updatePasswordForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePassword.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updatePassword.form = updatePasswordForm

/**
* @see \App\Http\Controllers\Api\MeController::updateAvatar
* @see app/Http/Controllers/Api/MeController.php:59
* @route '/api/me/avatar'
*/
export const updateAvatar = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateAvatar.url(options),
    method: 'post',
})

updateAvatar.definition = {
    methods: ["post"],
    url: '/api/me/avatar',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\MeController::updateAvatar
* @see app/Http/Controllers/Api/MeController.php:59
* @route '/api/me/avatar'
*/
updateAvatar.url = (options?: RouteQueryOptions) => {
    return updateAvatar.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\MeController::updateAvatar
* @see app/Http/Controllers/Api/MeController.php:59
* @route '/api/me/avatar'
*/
updateAvatar.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateAvatar.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\MeController::updateAvatar
* @see app/Http/Controllers/Api/MeController.php:59
* @route '/api/me/avatar'
*/
const updateAvatarForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateAvatar.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\MeController::updateAvatar
* @see app/Http/Controllers/Api/MeController.php:59
* @route '/api/me/avatar'
*/
updateAvatarForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateAvatar.url(options),
    method: 'post',
})

updateAvatar.form = updateAvatarForm

/**
* @see \App\Http\Controllers\Api\MeController::destroyAvatar
* @see app/Http/Controllers/Api/MeController.php:73
* @route '/api/me/avatar'
*/
export const destroyAvatar = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyAvatar.url(options),
    method: 'delete',
})

destroyAvatar.definition = {
    methods: ["delete"],
    url: '/api/me/avatar',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\MeController::destroyAvatar
* @see app/Http/Controllers/Api/MeController.php:73
* @route '/api/me/avatar'
*/
destroyAvatar.url = (options?: RouteQueryOptions) => {
    return destroyAvatar.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\MeController::destroyAvatar
* @see app/Http/Controllers/Api/MeController.php:73
* @route '/api/me/avatar'
*/
destroyAvatar.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyAvatar.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Api\MeController::destroyAvatar
* @see app/Http/Controllers/Api/MeController.php:73
* @route '/api/me/avatar'
*/
const destroyAvatarForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyAvatar.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\MeController::destroyAvatar
* @see app/Http/Controllers/Api/MeController.php:73
* @route '/api/me/avatar'
*/
destroyAvatarForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyAvatar.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroyAvatar.form = destroyAvatarForm

/**
* @see \App\Http\Controllers\Api\MeController::destroy
* @see app/Http/Controllers/Api/MeController.php:87
* @route '/api/me'
*/
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/me',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\MeController::destroy
* @see app/Http/Controllers/Api/MeController.php:87
* @route '/api/me'
*/
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\MeController::destroy
* @see app/Http/Controllers/Api/MeController.php:87
* @route '/api/me'
*/
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Api\MeController::destroy
* @see app/Http/Controllers/Api/MeController.php:87
* @route '/api/me'
*/
const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\MeController::destroy
* @see app/Http/Controllers/Api/MeController.php:87
* @route '/api/me'
*/
destroyForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const MeController = { show, update, updatePassword, updateAvatar, destroyAvatar, destroy }

export default MeController
import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::index
* @see app/Http/Controllers/Admin/LegalDocumentController.php:15
* @route '/admin/legal-documents'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/legal-documents',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::index
* @see app/Http/Controllers/Admin/LegalDocumentController.php:15
* @route '/admin/legal-documents'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::index
* @see app/Http/Controllers/Admin/LegalDocumentController.php:15
* @route '/admin/legal-documents'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::index
* @see app/Http/Controllers/Admin/LegalDocumentController.php:15
* @route '/admin/legal-documents'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::index
* @see app/Http/Controllers/Admin/LegalDocumentController.php:15
* @route '/admin/legal-documents'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::index
* @see app/Http/Controllers/Admin/LegalDocumentController.php:15
* @route '/admin/legal-documents'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::index
* @see app/Http/Controllers/Admin/LegalDocumentController.php:15
* @route '/admin/legal-documents'
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
* @see \App\Http\Controllers\Admin\LegalDocumentController::edit
* @see app/Http/Controllers/Admin/LegalDocumentController.php:31
* @route '/admin/legal-documents/{legalDocument}/edit'
*/
export const edit = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/legal-documents/{legalDocument}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::edit
* @see app/Http/Controllers/Admin/LegalDocumentController.php:31
* @route '/admin/legal-documents/{legalDocument}/edit'
*/
edit.url = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { legalDocument: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
        args = { legalDocument: args.slug }
    }

    if (Array.isArray(args)) {
        args = {
            legalDocument: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        legalDocument: typeof args.legalDocument === 'object'
        ? args.legalDocument.slug
        : args.legalDocument,
    }

    return edit.definition.url
            .replace('{legalDocument}', parsedArgs.legalDocument.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::edit
* @see app/Http/Controllers/Admin/LegalDocumentController.php:31
* @route '/admin/legal-documents/{legalDocument}/edit'
*/
edit.get = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::edit
* @see app/Http/Controllers/Admin/LegalDocumentController.php:31
* @route '/admin/legal-documents/{legalDocument}/edit'
*/
edit.head = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::edit
* @see app/Http/Controllers/Admin/LegalDocumentController.php:31
* @route '/admin/legal-documents/{legalDocument}/edit'
*/
const editForm = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::edit
* @see app/Http/Controllers/Admin/LegalDocumentController.php:31
* @route '/admin/legal-documents/{legalDocument}/edit'
*/
editForm.get = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::edit
* @see app/Http/Controllers/Admin/LegalDocumentController.php:31
* @route '/admin/legal-documents/{legalDocument}/edit'
*/
editForm.head = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\LegalDocumentController::update
* @see app/Http/Controllers/Admin/LegalDocumentController.php:50
* @route '/admin/legal-documents/{legalDocument}'
*/
export const update = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/legal-documents/{legalDocument}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::update
* @see app/Http/Controllers/Admin/LegalDocumentController.php:50
* @route '/admin/legal-documents/{legalDocument}'
*/
update.url = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { legalDocument: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
        args = { legalDocument: args.slug }
    }

    if (Array.isArray(args)) {
        args = {
            legalDocument: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        legalDocument: typeof args.legalDocument === 'object'
        ? args.legalDocument.slug
        : args.legalDocument,
    }

    return update.definition.url
            .replace('{legalDocument}', parsedArgs.legalDocument.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::update
* @see app/Http/Controllers/Admin/LegalDocumentController.php:50
* @route '/admin/legal-documents/{legalDocument}'
*/
update.put = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::update
* @see app/Http/Controllers/Admin/LegalDocumentController.php:50
* @route '/admin/legal-documents/{legalDocument}'
*/
update.patch = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::update
* @see app/Http/Controllers/Admin/LegalDocumentController.php:50
* @route '/admin/legal-documents/{legalDocument}'
*/
const updateForm = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::update
* @see app/Http/Controllers/Admin/LegalDocumentController.php:50
* @route '/admin/legal-documents/{legalDocument}'
*/
updateForm.put = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\LegalDocumentController::update
* @see app/Http/Controllers/Admin/LegalDocumentController.php:50
* @route '/admin/legal-documents/{legalDocument}'
*/
updateForm.patch = (args: { legalDocument: string | { slug: string } } | [legalDocument: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const LegalDocumentController = { index, edit, update }

export default LegalDocumentController
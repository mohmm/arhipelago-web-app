<?php

/*
| The validation messages the app can show in Arabic. Rules missing here fall
| back to English.
*/

return [
    'accepted' => 'يجب قبول :attribute.',
    'between' => [
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و:max.',
        'file' => 'يجب أن يكون حجم :attribute بين :min و:max كيلوبايت.',
        'string' => 'يجب أن يكون طول :attribute بين :min و:max حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على عدد عناصر بين :min و:max.',
    ],
    'boolean' => 'يجب أن تكون قيمة :attribute صحيحة أو خاطئة.',
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صالحًا.',
    'enum' => 'قيمة :attribute المختارة غير صالحة.',
    'exists' => 'قيمة :attribute المختارة غير موجودة.',
    'image' => 'يجب أن يكون :attribute صورة.',
    'in' => 'قيمة :attribute المختارة غير صالحة.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'lowercase' => 'يجب أن يكون :attribute بأحرف صغيرة.',
    'max' => [
        'numeric' => 'يجب ألا تزيد قيمة :attribute عن :max.',
        'file' => 'يجب ألا يزيد حجم :attribute عن :max كيلوبايت.',
        'string' => 'يجب ألا يزيد طول :attribute عن :max حرفًا.',
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :max عناصر.',
    ],
    'mimes' => 'يجب أن يكون :attribute ملفًا من نوع: :values.',
    'min' => [
        'numeric' => 'يجب ألا تقل قيمة :attribute عن :min.',
        'file' => 'يجب ألا يقل حجم :attribute عن :min كيلوبايت.',
        'string' => 'يجب ألا يقل طول :attribute عن :min أحرف.',
        'array' => 'يجب أن يحتوي :attribute على :min عناصر على الأقل.',
    ],
    'numeric' => 'يجب أن يكون :attribute رقمًا.',
    'password' => [
        'letters' => 'يجب أن يحتوي :attribute على حرف واحد على الأقل.',
        'mixed' => 'يجب أن يحتوي :attribute على حرف كبير وحرف صغير على الأقل.',
        'numbers' => 'يجب أن يحتوي :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن يحتوي :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'ظهر :attribute في تسريب بيانات. يرجى اختيار :attribute مختلفة.',
    ],
    'prohibits' => 'لا يمكن استخدام :attribute مع :other.',
    'required' => 'حقل :attribute مطلوب.',
    'required_if' => 'حقل :attribute مطلوب عندما يكون :other :value.',
    'required_with' => 'حقل :attribute مطلوب عند وجود :values.',
    'string' => 'يجب أن يكون :attribute نصًا.',
    'unique' => 'قيمة :attribute مستخدمة من قبل.',
    'uploaded' => 'تعذر رفع :attribute.',

    'attributes' => [
        'avatar' => 'الصورة الشخصية',
        'bio' => 'النبذة',
        'body' => 'النص',
        'city' => 'المدينة',
        'content' => 'الرسالة',
        'current_password' => 'كلمة المرور الحالية',
        'email' => 'البريد الإلكتروني',
        'first_name' => 'الاسم الأول',
        'last_name' => 'اسم العائلة',
        'locale' => 'اللغة',
        'message' => 'الرسالة',
        'password' => 'كلمة المرور',
        'photo' => 'الصورة',
        'rating' => 'التقييم',
        'travel_preferences' => 'تفضيلات السفر',
    ],
];

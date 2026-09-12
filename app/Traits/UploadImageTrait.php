<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait UploadImageTrait
{
    /**
     * رفع صورة وربطها بالموديل عبر اسم العلاقة المحدد ديناميكياً
     *
     * @param Request $request
     * @param Model $model
     * @param string $relationName اسم العلاقة المعرّفة في الموديل (مثل 'image' أو 'images')
     * @param string $fieldName اسم حقل الملف في الطلب
     * @param string $folderName اسم المجلد للتخزين
     * @return bool
     */
    public function uploadImage(
        Request $request,
        Model $model,
        string $relationName = 'image',
        string $fieldName = 'image',
        string $folderName = 'images'
    ): bool {
        if ($request->hasFile($fieldName)) {
            $image = $request->file($fieldName);
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path($folderName), $imageName);

            $imagePath = $folderName . '/' . $imageName;

            // التأكد من وجود العلاقة في الموديل قبل تنفيذ أي أمر
            if (!method_exists($model, $relationName)) {
                return false;
            }

            // فحص هل الموديل يمتلك صورة سابقة عبر العلاقة المحددة ديناميكياً
            if ($model->$relationName) {
                // إذا كانت العلاقة مفردة (morphOne) نحدث السجل، وإذا كانت متعددة (morphMany) تنشئ سجلاً جديداً
                $model->$relationName()->updateOrCreate([], ['url' => $imagePath]);
            } else {
                $model->$relationName()->create(['url' => $imagePath]);
            }

            return true;
        }

        return false;
    }
}
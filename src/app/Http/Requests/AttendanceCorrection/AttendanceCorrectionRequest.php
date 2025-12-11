<?php

namespace App\Http\Requests\AttendanceCorrection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // 勤怠ID
            'attendance_id' => 'required|exists:attendances,id',

            // 備考
            'reason' => 'required|string|max:500',

            // 出勤・退勤（H:i形式）
            'work_in' => 'nullable|date_format:H:i',
            'work_out' => 'nullable|date_format:H:i',

            // 既存休憩
            'breaks.*.start' => 'nullable|date_format:H:i',
            'breaks.*.end'   => 'nullable|date_format:H:i',

            // 新規休憩
            'breaks_new.start' => 'nullable|date_format:H:i',
            'breaks_new.end'   => 'nullable|date_format:H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => '備考を記入してください',
            'reason.max' => '備考は500文字以内で入力してください',

            'work_in.date_format' => '出勤時間は「HH:MM」の形式で入力してください',
            'work_out.date_format' => '退勤時間は「HH:MM」の形式で入力してください',

            'breaks.*.start.date_format' => '休憩開始時間は「HH:MM」の形式で入力してください',
            'breaks.*.end.date_format' => '休憩終了時間は「HH:MM」の形式で入力してください',

            'breaks_new.start.date_format' => '休憩開始時間は「HH:MM」の形式で入力してください',
            'breaks_new.end.date_format' => '休憩終了時間は「HH:MM」の形式で入力してください',
        ];
    }

    /**
     * 追加の時系列バリデーション（FN029 対応）
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $in  = $this->input('work_in');
            $out = $this->input('work_out');
            $breaks = $this->input('breaks', []);
            $newBreakStart = $this->input('breaks_new.start');
            $newBreakEnd   = $this->input('breaks_new.end');

            // ================================
            // ① 出勤 > 退勤 → エラー
            // ================================
            if ($in && $out && $in > $out) {
                $validator->errors()->add('work_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // ================================
            // ② 休憩の時系列
            // ================================
            foreach ($breaks as $i => $b) {
                $start = $b['start'] ?? null;
                $end   = $b['end'] ?? null;

                if ($start && $in && $start < $in) {
                    $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                }

                if ($end && $out && $end > $out) {
                    $validator->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です');
                }

                if ($start && $end && $start > $end) {
                    $validator->errors()->add("breaks.$i.end", '休憩時間が不適切な値です');
                }
            }

            // ================================
            // ③ 新規休憩の時系列
            // ================================
            if ($newBreakStart && $in && $newBreakStart < $in) {
                $validator->errors()->add("breaks_new.start", '休憩時間が不適切な値です');
            }

            if ($newBreakEnd && $out && $newBreakEnd > $out) {
                $validator->errors()->add("breaks_new.end", '休憩時間もしくは退勤時間が不適切な値です');
            }

            if ($newBreakStart && $newBreakEnd && $newBreakStart > $newBreakEnd) {
                $validator->errors()->add("breaks_new.end", '休憩時間が不適切な値です');
            }
        });
    }
}


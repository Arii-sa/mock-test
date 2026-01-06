<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    /**
     * 管理者なので常にOK
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 入力チェック
     */
    public function rules(): array
    {
        return [
            // 出勤・退勤
            'work_in'  => 'nullable|date_format:H:i',
            'work_out' => 'nullable|date_format:H:i',

            // 休憩
            'breaks.*.start' => 'nullable|date_format:H:i',
            'breaks.*.end'   => 'nullable|date_format:H:i',

            // 備考
            'reason' => 'required|string',
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'reason.required' => '備考を記入してください',
        ];
    }

    /**
     * 時系列チェック（FN039 対応）
     */
    public function withValidator($validator)
    {
        $validator->after(function ($v) {

            $in  = $this->input('work_in');
            $out = $this->input('work_out');
            $breaks = $this->input('breaks', []);

            // ① 出勤 > 退勤
            if ($in && $out && $in > $out) {
                $v->errors()->add(
                    'work_out',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            // ② 休憩チェック
            foreach ($breaks as $i => $b) {
                $start = $b['start'] ?? null;
                $end   = $b['end'] ?? null;

                if ($start && $in && $start < $in) {
                    $v->errors()->add(
                        "breaks.$i.start",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($start && $out && $start > $out) {
                    $v->errors()->add(
                        "breaks.$i.start",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($end && $out && $end > $out) {
                    $v->errors()->add(
                        "breaks.$i.end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }

                if ($start && $end && $start > $end) {
                    $v->errors()->add(
                        "breaks.$i.end",
                        '休憩時間が不適切な値です'
                    );
                }
            }
        });
    }
}


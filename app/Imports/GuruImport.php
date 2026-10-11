<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    public int $bilangBerjaya = 0;

    public int $bilangAkaunDicipta = 0;

    /** @var array<int, string> */
    public array $dilangkau = [];

    public function model(array $row): ?Model
    {
        $nama = trim((string) ($row['nama'] ?? ''));

        if ($nama === '') {
            return null;
        }

        $noPekerja = $this->kosongKeNull($row['no_pekerja'] ?? null);

        if ($noPekerja && Guru::where('no_pekerja', $noPekerja)->exists()) {
            $this->dilangkau[] = "No. Pekerja {$noPekerja}: dilangkau kerana sudah wujud dalam sistem.";

            return null;
        }

        $email = $this->kosongKeNull($row['emel'] ?? null);

        if ($email && User::where('email', $email)->exists()) {
            $this->dilangkau[] = "{$nama}: emel \"{$email}\" sudah digunakan oleh akaun lain, guru ditambah tanpa akaun log masuk.";
            $email = null;
        }

        $guru = Guru::create([
            'nama' => $nama,
            'no_pekerja' => $noPekerja,
            'no_kp' => $this->kosongKeNull($row['no_kp'] ?? null),
            'no_telefon' => $this->kosongKeNull($row['no_telefon'] ?? null),
            'status' => $this->uraiStatus($row['status'] ?? null),
        ]);

        $this->bilangBerjaya++;

        if ($email) {
            $guru->ciptaAtauResetAkaun($email);
            $this->bilangAkaunDicipta++;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'emel' => ['nullable', 'email'],
        ];
    }

    private function kosongKeNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function uraiStatus(mixed $value): string
    {
        $value = mb_strtolower(trim((string) $value));

        return match ($value) {
            'tidak aktif', 'inactive' => 'Tidak Aktif',
            default => 'Aktif',
        };
    }
}

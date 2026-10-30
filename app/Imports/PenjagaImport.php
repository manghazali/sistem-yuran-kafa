<?php

namespace App\Imports;

use App\Models\Penjaga;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PenjagaImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    public int $bilangBerjaya = 0;

    public int $bilangEmelMenunggu = 0;

    /** @var array<int, string> */
    public array $dilangkau = [];

    public function model(array $row): ?Model
    {
        $nama = trim((string) ($row['nama'] ?? ''));

        if ($nama === '') {
            return null;
        }

        $noTelefon = trim((string) ($row['no_telefon'] ?? ''));

        if ($noTelefon === '') {
            return null;
        }

        $hubungan = $this->uraiHubungan($row['hubungan'] ?? null);

        if (! $hubungan) {
            $this->dilangkau[] = "{$nama}: hubungan tidak sah (guna Bapa/Ibu/Penjaga Sah), baris dilangkau.";

            return null;
        }

        $email = $this->kosongKeNull($row['emel'] ?? null);

        if ($email && User::where('email', $email)->exists()) {
            $this->dilangkau[] = "{$nama}: emel \"{$email}\" sudah digunakan oleh akaun lain, penjaga ditambah tanpa akaun portal.";
            $email = null;
        }

        $penjaga = Penjaga::create([
            'nama' => $nama,
            'no_kp' => $this->kosongKeNull($row['no_kp'] ?? null),
            'no_telefon' => $noTelefon,
            'email' => $email,
            'alamat' => $this->kosongKeNull($row['alamat'] ?? null),
            'hubungan' => $hubungan,
        ]);

        $this->bilangBerjaya++;

        // Akaun dan emel log masuk dihantar kemudian melalui butang "Hantar Emel Akaun".
        if ($email) {
            $this->bilangEmelMenunggu++;
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
            'no_telefon' => ['required', 'string', 'max:20'],
        ];
    }

    private function kosongKeNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function uraiHubungan(mixed $value): ?string
    {
        $value = mb_strtolower(trim((string) $value));

        return match ($value) {
            'bapa', 'ayah', 'father' => 'Bapa',
            'ibu', 'emak', 'mother' => 'Ibu',
            'penjaga sah', 'penjaga', 'guardian' => 'Penjaga Sah',
            default => null,
        };
    }
}

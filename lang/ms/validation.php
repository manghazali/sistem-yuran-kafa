<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Pengesahan (Validation)
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut mengandungi mesej ralat lalai yang digunakan oleh
    | kelas Validator. Sebahagian peraturan ini mempunyai beberapa versi
    | seperti peraturan saiz. Anda boleh ubah mesej ini mengikut keperluan.
    |
    */

    'accepted' => ':attribute mesti diterima.',
    'accepted_if' => ':attribute mesti diterima apabila :other adalah :value.',
    'active_url' => ':attribute mesti URL yang sah.',
    'after' => ':attribute mesti tarikh selepas :date.',
    'after_or_equal' => ':attribute mesti tarikh selepas atau sama dengan :date.',
    'alpha' => ':attribute hanya boleh mengandungi huruf.',
    'alpha_dash' => ':attribute hanya boleh mengandungi huruf, nombor, sengkang dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh mengandungi huruf dan nombor.',
    'any_of' => ':attribute tidak sah.',
    'array' => ':attribute mestilah dalam bentuk senarai (array).',
    'array_keys' => ':attribute hanya boleh mengandungi kunci berikut: :values.',
    'ascii' => ':attribute hanya boleh mengandungi aksara dan simbol alfanumerik bait tunggal.',
    'base64' => ':attribute mesti rentetan Base64 yang sah.',
    'before' => ':attribute mesti tarikh sebelum :date.',
    'before_or_equal' => ':attribute mesti tarikh sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':attribute mesti mengandungi antara :min hingga :max item.',
        'file' => ':attribute mesti antara :min hingga :max kilobait.',
        'numeric' => ':attribute mesti antara :min hingga :max.',
        'string' => ':attribute mesti antara :min hingga :max aksara.',
    ],
    'boolean' => ':attribute mesti benar atau salah.',
    'can' => ':attribute mengandungi nilai yang tidak dibenarkan.',
    'confirmed' => 'Pengesahan :attribute tidak sepadan.',
    'contains' => ':attribute tidak mengandungi nilai yang diperlukan.',
    'current_password' => 'Kata laluan tidak betul.',
    'date' => ':attribute mesti tarikh yang sah.',
    'date_equals' => ':attribute mesti tarikh yang sama dengan :date.',
    'date_format' => ':attribute tidak sepadan dengan format :format.',
    'decimal' => ':attribute mesti mempunyai :decimal tempat perpuluhan.',
    'declined' => ':attribute mesti ditolak.',
    'declined_if' => ':attribute mesti ditolak apabila :other adalah :value.',
    'different' => ':attribute dan :other mesti berbeza.',
    'digits' => ':attribute mesti :digits digit.',
    'digits_between' => ':attribute mesti antara :min hingga :max digit.',
    'dimensions' => ':attribute mempunyai dimensi imej yang tidak sah.',
    'distinct' => ':attribute mempunyai nilai yang berulang.',
    'doesnt_contain' => ':attribute tidak boleh mengandungi mana-mana daripada: :values.',
    'doesnt_end_with' => ':attribute tidak boleh berakhir dengan salah satu daripada: :values.',
    'doesnt_start_with' => ':attribute tidak boleh bermula dengan salah satu daripada: :values.',
    'email' => ':attribute mesti alamat emel yang sah.',
    'encoding' => ':attribute mesti dienkod dalam :encoding.',
    'ends_with' => ':attribute mesti berakhir dengan salah satu daripada: :values.',
    'enum' => ':attribute yang dipilih tidak sah.',
    'exists' => ':attribute yang dipilih tidak sah.',
    'extensions' => ':attribute mesti mempunyai salah satu sambungan berikut: :values.',
    'file' => ':attribute mesti fail.',
    'filled' => ':attribute mesti mempunyai nilai.',
    'gt' => [
        'array' => ':attribute mesti mempunyai lebih daripada :value item.',
        'file' => ':attribute mesti lebih besar daripada :value kilobait.',
        'numeric' => ':attribute mesti lebih besar daripada :value.',
        'string' => ':attribute mesti lebih besar daripada :value aksara.',
    ],
    'gte' => [
        'array' => ':attribute mesti mempunyai :value item atau lebih.',
        'file' => ':attribute mesti lebih besar atau sama dengan :value kilobait.',
        'numeric' => ':attribute mesti lebih besar atau sama dengan :value.',
        'string' => ':attribute mesti lebih besar atau sama dengan :value aksara.',
    ],
    'hex_color' => ':attribute mesti warna heksadesimal yang sah.',
    'image' => ':attribute mesti imej.',
    'in' => ':attribute yang dipilih tidak sah.',
    'in_array' => ':attribute mesti wujud dalam :other.',
    'in_array_keys' => ':attribute mesti mengandungi sekurang-kurangnya salah satu kunci berikut: :values.',
    'integer' => ':attribute mesti nombor bulat.',
    'ip' => ':attribute mesti alamat IP yang sah.',
    'ipv4' => ':attribute mesti alamat IPv4 yang sah.',
    'ipv6' => ':attribute mesti alamat IPv6 yang sah.',
    'json' => ':attribute mesti rentetan JSON yang sah.',
    'list' => ':attribute mesti dalam bentuk senarai.',
    'lowercase' => ':attribute mesti huruf kecil.',
    'lt' => [
        'array' => ':attribute mesti mempunyai kurang daripada :value item.',
        'file' => ':attribute mesti kurang daripada :value kilobait.',
        'numeric' => ':attribute mesti kurang daripada :value.',
        'string' => ':attribute mesti kurang daripada :value aksara.',
    ],
    'lte' => [
        'array' => ':attribute tidak boleh mempunyai lebih daripada :value item.',
        'file' => ':attribute mesti kurang atau sama dengan :value kilobait.',
        'numeric' => ':attribute mesti kurang atau sama dengan :value.',
        'string' => ':attribute mesti kurang atau sama dengan :value aksara.',
    ],
    'mac_address' => ':attribute mesti alamat MAC yang sah.',
    'max' => [
        'array' => ':attribute tidak boleh mempunyai lebih daripada :max item.',
        'file' => ':attribute tidak boleh lebih besar daripada :max kilobait.',
        'numeric' => ':attribute tidak boleh lebih besar daripada :max.',
        'string' => ':attribute tidak boleh lebih besar daripada :max aksara.',
    ],
    'max_digits' => ':attribute tidak boleh mempunyai lebih daripada :max digit.',
    'mimes' => ':attribute mesti fail jenis: :values.',
    'mimetypes' => ':attribute mesti fail jenis: :values.',
    'min' => [
        'array' => ':attribute mesti mempunyai sekurang-kurangnya :min item.',
        'file' => ':attribute mesti sekurang-kurangnya :min kilobait.',
        'numeric' => ':attribute mesti sekurang-kurangnya :min.',
        'string' => ':attribute mesti sekurang-kurangnya :min aksara.',
    ],
    'min_digits' => ':attribute mesti sekurang-kurangnya :min digit.',
    'missing' => ':attribute mesti tidak hadir.',
    'missing_if' => ':attribute mesti tidak hadir apabila :other adalah :value.',
    'missing_unless' => ':attribute mesti tidak hadir melainkan :other adalah :value.',
    'missing_with' => ':attribute mesti tidak hadir apabila :values hadir.',
    'missing_with_all' => ':attribute mesti tidak hadir apabila :values hadir.',
    'multiple_of' => ':attribute mesti gandaan :value.',
    'not_in' => ':attribute yang dipilih tidak sah.',
    'not_regex' => 'Format :attribute tidak sah.',
    'numeric' => ':attribute mesti nombor.',
    'password' => [
        'letters' => ':attribute mesti mengandungi sekurang-kurangnya satu huruf.',
        'mixed' => ':attribute mesti mengandungi sekurang-kurangnya satu huruf besar dan satu huruf kecil.',
        'numbers' => ':attribute mesti mengandungi sekurang-kurangnya satu nombor.',
        'symbols' => ':attribute mesti mengandungi sekurang-kurangnya satu simbol.',
        'uncompromised' => ':attribute yang diberikan telah didedahkan dalam kebocoran data. Sila pilih :attribute yang lain.',
    ],
    'present' => ':attribute mesti hadir.',
    'present_if' => ':attribute mesti hadir apabila :other adalah :value.',
    'present_unless' => ':attribute mesti hadir melainkan :other adalah :value.',
    'present_with' => ':attribute mesti hadir apabila :values hadir.',
    'present_with_all' => ':attribute mesti hadir apabila :values hadir.',
    'prohibited' => ':attribute tidak dibenarkan.',
    'prohibited_if' => ':attribute tidak dibenarkan apabila :other adalah :value.',
    'prohibited_if_accepted' => ':attribute tidak dibenarkan apabila :other diterima.',
    'prohibited_if_declined' => ':attribute tidak dibenarkan apabila :other ditolak.',
    'prohibited_unless' => ':attribute tidak dibenarkan melainkan :other dalam :values.',
    'prohibits' => ':attribute menghalang :other daripada hadir.',
    'regex' => 'Format :attribute tidak sah.',
    'required' => ':attribute diperlukan.',
    'required_array_keys' => ':attribute mesti mengandungi entri untuk: :values.',
    'required_if' => ':attribute diperlukan apabila :other adalah :value.',
    'required_if_accepted' => ':attribute diperlukan apabila :other diterima.',
    'required_if_declined' => ':attribute diperlukan apabila :other ditolak.',
    'required_unless' => ':attribute diperlukan melainkan :other dalam :values.',
    'required_with' => ':attribute diperlukan apabila :values hadir.',
    'required_with_all' => ':attribute diperlukan apabila :values hadir.',
    'required_without' => ':attribute diperlukan apabila :values tidak hadir.',
    'required_without_all' => ':attribute diperlukan apabila tiada :values yang hadir.',
    'same' => ':attribute mesti sepadan dengan :other.',
    'size' => [
        'array' => ':attribute mesti mengandungi :size item.',
        'file' => ':attribute mesti :size kilobait.',
        'numeric' => ':attribute mesti :size.',
        'string' => ':attribute mesti :size aksara.',
    ],
    'starts_with' => ':attribute mesti bermula dengan salah satu daripada: :values.',
    'string' => ':attribute mesti rentetan.',
    'timezone' => ':attribute mesti zon masa yang sah.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => ':attribute gagal dimuat naik.',
    'uppercase' => ':attribute mesti huruf besar.',
    'url' => ':attribute mesti URL yang sah.',
    'ulid' => ':attribute mesti ULID yang sah.',
    'uuid' => ':attribute mesti UUID yang sah.',

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa Pengesahan Tersuai
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'mesej-tersuai',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama Atribut Tersuai
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut menggantikan nama medan (:attribute) dengan nama
    | yang lebih mudah difahami, contohnya "No. Kad Pengenalan" bagi "no_kp".
    |
    */

    'attributes' => [
        'name' => 'Nama',
        'nama' => 'Nama',
        'email' => 'Emel',
        'emel' => 'Emel',
        'password' => 'Kata Laluan',
        'current_password' => 'Kata Laluan Semasa',
        'password_confirmation' => 'Pengesahan Kata Laluan',
        'login_type' => 'Jenis Log Masuk',
        'no_kp' => 'No. Kad Pengenalan',
        'no_telefon' => 'No. Telefon',
        'no_pekerja' => 'No. Pekerja',
        'no_daftar' => 'No. Daftar',
        'alamat' => 'Alamat',
        'hubungan' => 'Hubungan',
        'jantina' => 'Jantina',
        'status' => 'Status',
        'tarikh_lahir' => 'Tarikh Lahir',
        'kelas_id' => 'Kelas',
        'darjah' => 'Tahun',
        'nama_kelas' => 'Nama Kelas',
        'pelajar_id' => 'Pelajar',
        'guru_id' => 'Guru',
        'penjaga_id' => 'Penjaga',
        'amaun' => 'Amaun',
        'catatan' => 'Catatan',
        'tarikh_bayaran' => 'Tarikh Bayaran',
        'tarikh_terima' => 'Tarikh Terima',
        'kaedah' => 'Kaedah Bayaran',
        'bulan' => 'Bulan',
        'tahun' => 'Tahun',
        'kategori_yuran_id' => 'Kategori Yuran',
        'nama_struktur' => 'Nama Struktur Yuran',
        'kitaran' => 'Kitaran',
        'tempoh_mula' => 'Tempoh Mula',
        'tempoh_tamat' => 'Tempoh Tamat',
        'sebab_tolak' => 'Sebab Tolak',
        'file' => 'Fail',
    ],

];

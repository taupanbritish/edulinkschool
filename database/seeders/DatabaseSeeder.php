<?php

namespace Database\Seeders;

use App\Models\OrganizationMember;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedSettings();
            $this->seedAdmin();
            $this->call(DemoAcademicSeeder::class);
            $this->seedStaffAndOrganization();
            $this->call(AnnouncementGallerySeeder::class);
            $this->call(ShowcaseSeeder::class);
            $this->call(AcademicContentSeeder::class);
            $this->call(StudentLifeSeeder::class);
            $this->call(PortalContentSeeder::class);
            $this->call(ContactMessageSeeder::class);
        });
    }

    private function seedSettings(): void
    {
        $settings = [
            'school_name' => 'SMA Edulink School',
            'school_short' => 'Edulink School',
            'tagline' => 'Berprestasi, Berkarakter, Berdaya Saing',
            'hero_title' => 'Belajar Bermakna, Tumbuh *Berkarakter*',
            'hero_subtitle' => 'SMA Edulink School mendampingi siswa meraih prestasi melalui pembelajaran bermakna, lingkungan aman, dan kemitraan erat bersama orang tua.',
            'npsn' => '20219876',
            'accreditation' => 'A',
            'founded_year' => '1985',
            'address' => 'Jl. Pendidikan Raya No. 1, Kec. Cibeunying Kaler, Kota Bandung 40123',
            'phone' => '(022) 720-1985',
            'whatsapp' => '0812-2000-1985',
            'email' => 'info@edulinkschool.sch.id',
            'office_hours' => 'Senin – Jumat, 07.00 – 15.00 WIB',
            'principal_name' => 'Dra. Siti Rahmawati, M.Pd.',
            'principal_welcome' => 'Assalamualaikum warahmatullahi wabarakatuh. Selamat datang di SMA Edulink School. Kami berkomitmen membangun generasi yang berprestasi, berkarakter, dan berdaya saing melalui pembelajaran yang bermakna, lingkungan yang aman, serta kemitraan erat dengan orang tua dan masyarakat.',
            'vision' => 'Terwujudnya peserta didik yang beriman, berprestasi, berkarakter Pancasila, dan siap bersaing di era global.',
            'mission' => "Menyelenggarakan pembelajaran aktif, inovatif, dan menyenangkan.\nMenumbuhkan budaya disiplin, jujur, dan saling menghormati.\nMengembangkan potensi akademik dan non-akademik peserta didik.\nMembangun kemitraan dengan orang tua, alumni, dan dunia usaha.",
            'history' => 'SMA Edulink School berdiri pada tahun 1985 di Kota Bandung sebagai sekolah menengah atas yang tumbuh bersama masyarakat. Selama empat dekade, sekolah ini telah meluluskan lebih dari 12.000 alumni yang berkontribusi di berbagai bidang. Sejak 2016, sekolah menerapkan Kurikulum Merdeka dengan penguatan projek profil pelajar Pancasila.',
            'curriculum_name' => 'Kurikulum Merdeka',
            'curriculum_description' => "SMA Edulink School menerapkan Kurikulum Merdeka dengan pembelajaran intrakurikuler yang beragam, projek penguatan profil pelajar Pancasila, dan fleksibilitas bagi siswa untuk memilih mata pelajaran sesuai minat, bakat, dan rencana studi lanjut.\n\nPendekatan pembelajaran berpusat pada siswa melalui diskusi, projek, praktik laboratorium, dan asesmen formatif. Guru berperan sebagai fasilitator yang mendampingi setiap siswa mencapai capaian pembelajaran.",
            'maps_url' => 'https://maps.google.com/?q=SMA+Edulink+School',
            'youtube_url' => 'https://www.youtube.com/watch?v=M7lc1UVf-VE',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        Setting::set('principal_photo_path', $this->copyPhoto('kepala-sekolah.jpg'));
    }

    private function seedAdmin(): User
    {
        $accounts = [
            ['Administrator Sekolah', 'admin@sekolah.id', 'admin123', User::ROLE_ADMIN, '0812-3456-7890'],
            ['Petugas Web Sekolah', 'petugas@sekolah.id', 'petugas123', User::ROLE_PETUGAS, '0812-3456-7891'],
            ['Dra. Siti Rahmawati, M.Pd.', 'kepsek@sekolah.id', 'kepsek123', User::ROLE_KEPALA_SEKOLAH, '0812-3456-7892'],
            ['Drs. Agus Priyanto, M.Pd.', 'wakasek@sekolah.id', 'wakasek123', User::ROLE_WAKASEK_KURIKULUM, '0812-3456-7893'],
        ];

        foreach ($accounts as [$name, $email, $password, $role, $phone]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'phone' => $phone,
                'is_active' => true,
            ]);
        }

        return User::where('email', 'admin@sekolah.id')->firstOrFail();
    }

    private function seedStaffAndOrganization(): void
    {
        $kepalaTu = Staff::create([
            'nip' => '198704152011012008',
            'name' => 'Wati Sumiati, S.A.P.',
            'photo_path' => $this->copyPhoto('staf-tu-wati.jpg'),
            'gender' => 'P',
            'position' => 'Kepala Tata Usaha',
            'phone' => '0813-7000-1001',
            'email' => 'tatausaha@sekolah.id',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Staff::create([
            'nip' => '199002202015011003',
            'name' => 'Hadi Supriyanto',
            'gender' => 'L',
            'position' => 'Staf Administrasi',
            'phone' => '0813-7000-1002',
            'email' => 'hadi.supriyanto@sekolah.id',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Staff::create([
            'nip' => '199508102019022005',
            'name' => 'Rina Marlina',
            'gender' => 'P',
            'position' => 'Staf Kesiswaan',
            'phone' => '0813-7000-1003',
            'email' => 'rina.marlina@sekolah.id',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $root = OrganizationMember::create([
            'name' => 'Dra. Siti Rahmawati, M.Pd.',
            'position' => 'Kepala Sekolah',
            'photo_path' => $this->copyPhoto('kepala-sekolah.jpg'),
            'sort_order' => 1,
        ]);

        OrganizationMember::create([
            'parent_id' => $root->id,
            'name' => 'Ahmad Fauzi, M.Pd.',
            'position' => 'Wakil Kepala Bidang Kurikulum',
            'sort_order' => 1,
        ]);

        OrganizationMember::create([
            'parent_id' => $root->id,
            'name' => 'Ratna Dewi, S.Si.',
            'position' => 'Wakil Kepala Bidang Kesiswaan',
            'sort_order' => 2,
        ]);

        OrganizationMember::create([
            'parent_id' => $root->id,
            'name' => 'Budi Santoso, S.Pd.',
            'position' => 'Wakil Kepala Bidang Sarana Prasarana',
            'photo_path' => $this->copyPhoto('guru-budi.jpg'),
            'sort_order' => 3,
        ]);

        $tuNode = OrganizationMember::create([
            'parent_id' => $root->id,
            'name' => $kepalaTu->name,
            'position' => 'Kepala Tata Usaha',
            'photo_path' => $kepalaTu->photo_path,
            'sort_order' => 4,
        ]);

        OrganizationMember::create([
            'parent_id' => $tuNode->id,
            'name' => 'Hadi Supriyanto',
            'position' => 'Staf Administrasi',
            'sort_order' => 1,
        ]);

        OrganizationMember::create([
            'parent_id' => $tuNode->id,
            'name' => 'Rina Marlina',
            'position' => 'Staf Kesiswaan',
            'sort_order' => 2,
        ]);

        OrganizationMember::create([
            'parent_id' => $root->id,
            'name' => 'Sri Wahyuni, S.Pd.',
            'position' => 'Koordinator Bimbingan Konseling',
            'photo_path' => $this->copyPhoto('guru-sri.jpg'),
            'sort_order' => 5,
        ]);
    }

    private function copyPhoto(string $file): ?string
    {
        $source = database_path('seeders/photos/'.$file);

        if (! is_file($source)) {
            return null;
        }

        $target = 'photos/demo/'.$file;

        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }

        return $target;
    }
}

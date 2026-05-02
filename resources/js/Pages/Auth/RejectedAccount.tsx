import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link } from '@inertiajs/react';
import { Ban } from 'lucide-react';

export default function RejectedAccount({
    rejectedReason,
}: {
    rejectedReason?: string | null;
}) {
    return (
        <GuestLayout>
            <Head title="Akun Ditolak" />

            <div className="text-center">
                <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-rose-50 text-rose-700 ring-1 ring-rose-100">
                    <Ban className="h-7 w-7" />
                </div>
                <h1 className="mt-5 text-xl font-semibold text-slate-950">
                    Akun Ditolak
                </h1>
                <p className="mt-3 text-sm leading-6 text-slate-600">
                    Akun Anda ditolak. Silakan hubungi admin operasional.
                </p>
                {rejectedReason && (
                    <div className="mt-4 rounded-md border border-rose-100 bg-rose-50 px-4 py-3 text-left text-sm text-rose-700">
                        {rejectedReason}
                    </div>
                )}
                <div className="mt-6">
                    <Link
                        as="button"
                        className="inline-flex items-center rounded-md border border-transparent bg-cyan-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-cyan-700 focus:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 active:bg-cyan-800"
                        href={route('logout')}
                        method="post"
                    >
                        Keluar
                    </Link>
                </div>
            </div>
        </GuestLayout>
    );
}

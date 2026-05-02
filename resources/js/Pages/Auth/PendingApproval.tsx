import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link } from '@inertiajs/react';
import { Clock3 } from 'lucide-react';

export default function PendingApproval() {
    return (
        <GuestLayout>
            <Head title="Menunggu Approval" />

            <div className="text-center">
                <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-amber-50 text-amber-700 ring-1 ring-amber-100">
                    <Clock3 className="h-7 w-7" />
                </div>
                <h1 className="mt-5 text-xl font-semibold text-slate-950">
                    Akun Menunggu Approval
                </h1>
                <p className="mt-3 text-sm leading-6 text-slate-600">
                    Akun Anda sedang menunggu approval dari Super Admin. Anda
                    akan dapat mengakses sistem setelah akun disetujui.
                </p>
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

import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { BranchSummary } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Register({ branches }: { branches: BranchSummary[] }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        branch_id: '',
        job_title: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Register" />

            <div className="mb-6">
                <h1 className="text-xl font-semibold text-slate-950">
                    Daftar Akun Cabang
                </h1>
                <p className="mt-2 text-sm leading-6 text-slate-500">
                    Akun baru akan menunggu approval Super Admin sebelum dapat
                    mengakses sistem.
                </p>
            </div>

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="name" value="Name" />

                    <TextInput
                        autoComplete="name"
                        className="mt-1 block w-full"
                        id="name"
                        isFocused={true}
                        name="name"
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        value={data.name}
                    />

                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        autoComplete="username"
                        className="mt-1 block w-full"
                        id="email"
                        name="email"
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        type="email"
                        value={data.email}
                    />

                    <InputError className="mt-2" message={errors.email} />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="branch_id" value="Branch" />

                    <select
                        className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                        id="branch_id"
                        name="branch_id"
                        onChange={(e) => setData('branch_id', e.target.value)}
                        required
                        value={data.branch_id}
                    >
                        <option value="">Pilih cabang</option>
                        {branches.map((branch) => (
                            <option key={branch.id} value={branch.id}>
                                {branch.name} ({branch.code})
                            </option>
                        ))}
                    </select>

                    <InputError className="mt-2" message={errors.branch_id} />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="job_title" value="Job Title" />

                    <TextInput
                        autoComplete="organization-title"
                        className="mt-1 block w-full"
                        id="job_title"
                        name="job_title"
                        onChange={(e) => setData('job_title', e.target.value)}
                        value={data.job_title}
                    />

                    <InputError className="mt-2" message={errors.job_title} />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        autoComplete="new-password"
                        className="mt-1 block w-full"
                        id="password"
                        name="password"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        type="password"
                        value={data.password}
                    />

                    <InputError className="mt-2" message={errors.password} />
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirm Password"
                    />

                    <TextInput
                        autoComplete="new-password"
                        className="mt-1 block w-full"
                        id="password_confirmation"
                        name="password_confirmation"
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        required
                        type="password"
                        value={data.password_confirmation}
                    />

                    <InputError
                        className="mt-2"
                        message={errors.password_confirmation}
                    />
                </div>

                <div className="mt-6 flex items-center justify-end">
                    <Link
                        className="rounded-md text-sm text-slate-600 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2"
                        href={route('login')}
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton className="ms-4" disabled={processing}>
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}

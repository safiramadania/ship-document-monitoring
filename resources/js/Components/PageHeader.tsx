import { ReactNode } from 'react';

type PageHeaderProps = {
    title: string;
    description?: string;
    actions?: ReactNode;
};

export default function PageHeader({
    title,
    description,
    actions,
}: PageHeaderProps) {
    return (
        <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p className="text-sm font-medium uppercase tracking-wide text-cyan-700">
                    Ship Document Monitoring
                </p>
                <h1 className="mt-2 text-2xl font-semibold text-slate-950">
                    {title}
                </h1>
                {description && (
                    <p className="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        {description}
                    </p>
                )}
            </div>
            {actions && <div className="flex items-center gap-2">{actions}</div>}
        </div>
    );
}

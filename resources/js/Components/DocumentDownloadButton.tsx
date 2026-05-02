import { Download } from 'lucide-react';

import { Button } from '@/Components/ui/button';

export default function DocumentDownloadButton({ href }: { href: string }) {
    return (
        <a href={href}>
            <Button size="sm" type="button" variant="outline">
                <Download className="h-4 w-4" />
                Download
            </Button>
        </a>
    );
}

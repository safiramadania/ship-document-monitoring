import { Eye } from 'lucide-react';

import { Button } from '@/Components/ui/button';

export default function DocumentPreviewButton({ href }: { href: string }) {
    return (
        <a href={href} target="_blank">
            <Button size="sm" type="button" variant="outline">
                <Eye className="h-4 w-4" />
                Preview
            </Button>
        </a>
    );
}

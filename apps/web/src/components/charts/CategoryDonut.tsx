'use client';

import { PieChart, Pie, Cell, Tooltip, ResponsiveContainer } from 'recharts';
import type { CategoryBreakdown } from '@/types/api';

const UNCATEGORIZED_COLOR = '#a1a1aa';

interface Props {
  data: CategoryBreakdown[];
}

export default function CategoryDonut({ data }: Props) {
  const slices = data
    .filter((b) => b.monthly_total > 0)
    .map((b) => ({
      name: b.name ?? 'Uncategorized',
      value: b.monthly_total,
      color: b.color ?? UNCATEGORIZED_COLOR,
    }));

  if (slices.length === 0) {
    return (
      <div className='flex items-center justify-center h-48 text-sm text-zinc-400'>
        No active subscriptions
      </div>
    );
  }

  return (
    <ResponsiveContainer width='100%' height={220}>
      <PieChart>
        <Pie
          data={slices}
          cx='50%'
          cy='50%'
          innerRadius={64}
          outerRadius={96}
          paddingAngle={2}
          dataKey='value'
        >
          {slices.map((slice, i) => (
            <Cell key={i} fill={slice.color} />
          ))}
        </Pie>
        <Tooltip
          formatter={(value) => [
            `$${Number(value).toFixed(2)}/mo`,
            'Monthly Cost',
          ]}
        />
      </PieChart>
    </ResponsiveContainer>
  );
}

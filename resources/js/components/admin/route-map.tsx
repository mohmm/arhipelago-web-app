import { usePage } from '@inertiajs/react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { useEffect, useMemo } from 'react';
import { MapContainer, Marker, Polyline, TileLayer, useMap } from 'react-leaflet';
import { decodePolyline6 } from '@/lib/polyline';
import type { SharedData } from '@/types';

export type RouteStop = { latitude: number; longitude: number; label: string };

function numberedIcon(number: number): L.DivIcon {
    return L.divIcon({
        className: '',
        html: `<span style="display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:9999px;background:#d63729;color:#fff;font:600 12px Inter,sans-serif;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.35)">${number}</span>`,
        iconSize: [26, 26],
        iconAnchor: [13, 13],
    });
}

function FitToRoute({ points }: { points: [number, number][] }) {
    const map = useMap();

    useEffect(() => {
        if (points.length > 0) {
            map.fitBounds(L.latLngBounds(points), { padding: [28, 28] });
        }
    }, [map, points]);

    return null;
}

/**
 * An experience on the map, as the app draws it: the measured road in red
 * and each stop as a numbered pin.
 */
export default function RouteMap({ polyline, stops }: { polyline: string | null; stops: RouteStop[] }) {
    const { mapTiles } = usePage<SharedData>().props;
    const line = useMemo(() => (polyline ? decodePolyline6(polyline) : []), [polyline]);
    const pins = useMemo(() => stops.map((stop): [number, number] => [stop.latitude, stop.longitude]), [stops]);
    const bounds = line.length > 0 ? line : pins;

    return (
        <div className="overflow-hidden rounded-xl border">
            <MapContainer center={bounds[0] ?? [45.6427, 25.5887]} zoom={11} scrollWheelZoom={false} className="h-80 w-full">
                <TileLayer url={mapTiles.url} attribution={mapTiles.attribution} />
                <FitToRoute points={bounds} />
                {line.length > 0 && (
                    <>
                        <Polyline positions={line} pathOptions={{ color: '#ffffff', weight: 8, opacity: 0.9 }} />
                        <Polyline positions={line} pathOptions={{ color: '#d63729', weight: 4 }} />
                    </>
                )}
                {stops.map((stop, index) => (
                    <Marker key={`${stop.latitude},${stop.longitude}`} position={[stop.latitude, stop.longitude]} icon={numberedIcon(index + 1)} title={stop.label} />
                ))}
            </MapContainer>
        </div>
    );
}

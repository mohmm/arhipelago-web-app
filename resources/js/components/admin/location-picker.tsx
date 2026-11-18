import { usePage } from '@inertiajs/react';
import 'leaflet/dist/leaflet.css';
import { useEffect } from 'react';
import { CircleMarker, MapContainer, TileLayer, useMap, useMapEvents } from 'react-leaflet';
import type { SharedData } from '@/types';

/** Brașov: where the map opens before a place has a position. */
const DEFAULT_CENTRE: [number, number] = [45.6427, 25.5887];

type Props = {
    latitude: number | null;
    longitude: number | null;
    onChange: (latitude: number, longitude: number) => void;
};

function ClickToPlace({ onChange }: Pick<Props, 'onChange'>) {
    useMapEvents({
        click: (event) => onChange(Number(event.latlng.lat.toFixed(6)), Number(event.latlng.lng.toFixed(6))),
    });

    return null;
}

function FollowMarker({ position }: { position: [number, number] | null }) {
    const map = useMap();

    useEffect(() => {
        if (position != null) {
            map.panTo(position);
        }
    }, [map, position]);

    return null;
}

/**
 * Where a place is: click the map to put the pin there.
 */
export default function LocationPicker({ latitude, longitude, onChange }: Props) {
    const { mapTiles } = usePage<SharedData>().props;
    const position: [number, number] | null = latitude != null && longitude != null ? [latitude, longitude] : null;

    return (
        <div className="overflow-hidden rounded-xl border">
            <MapContainer center={position ?? DEFAULT_CENTRE} zoom={position ? 15 : 9} scrollWheelZoom className="h-72 w-full">
                <TileLayer url={mapTiles.url} attribution={mapTiles.attribution} />
                <ClickToPlace onChange={onChange} />
                <FollowMarker position={position} />
                {position && <CircleMarker center={position} radius={9} pathOptions={{ color: '#ffffff', weight: 3, fillColor: '#d63729', fillOpacity: 1 }} />}
            </MapContainer>
        </div>
    );
}

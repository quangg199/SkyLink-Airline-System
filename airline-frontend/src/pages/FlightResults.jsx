import { useState, useEffect } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import { AirplaneLanding, MagnifyingGlass } from "@phosphor-icons/react";
// eslint-disable-next-line no-unused-vars
import { motion } from "motion/react";
import FlightCard from "../components/flight/FlightCard";
import FlightFilterSidebar from "../components/flight/FlightFilterSidebar";
import BackButton from "../components/BackButton";
import Navbar from "../components/Navbar";

/**
 * FlightResults Smart Component (Container)
 * Handles API fetching, state management, and layout orchestration.
 * Design: Premium Light Theme, Sticky Parallel Sidebar, Motion 7, Density 5
 */
export default function FlightResults() {
  const [flights, setFlights] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [filters, setFilters] = useState({
    sort: 'price_asc',
    times: [],
  });

  const [searchParams, setSearchParams] = useState(null);
  // eslint-disable-next-line no-unused-vars
  const [discountApplied, setDiscountApplied] = useState(null);
  const [bookingStage, setBookingStage] = useState('outbound');
  const [outboundFlight, setOutboundFlight] = useState(null);

  const navigate = useNavigate();

  useEffect(() => {
    // Đọc tiêu chí tìm kiếm từ localStorage (được lưu bởi HomePage)
    const stored = localStorage.getItem("search_params");
    const parsed = stored ? JSON.parse(stored) : null;
    setSearchParams(parsed);
    fetchFlights(parsed);
  }, []);

  const fetchFlights = async (params) => {
    setLoading(true);
    try {
      // Xây dựng query string từ tiêu chí tìm kiếm
      const queryParts = [];
      if (params?.departure) queryParts.push(`from=${encodeURIComponent(params.departure)}`);
      if (params?.arrival)   queryParts.push(`to=${encodeURIComponent(params.arrival)}`);
      if (params?.date)      queryParts.push(`date=${encodeURIComponent(params.date)}`);

      // Chuyển đổi tripType: FE dùng 'one-way'/'round-trip' (hyphen)
      // → BE cần 'one_way'/'round_trip' (underscore)
      if (params?.tripType) {
        const backendTripType = params.tripType.replace('-', '_');
        queryParts.push(`trip_type=${encodeURIComponent(backendTripType)}`);
      }

      const queryString = queryParts.length > 0 ? `?${queryParts.join("&")}` : "";
      const response = await axios.get(`http://127.0.0.1:8000/api/flights${queryString}`);
      setFlights(response.data.data || []);
      setDiscountApplied(response.data.discount_applied || null);
      setError(null);
    } catch {
      setError("Không thể tải danh sách chuyến bay. Vui lòng thử lại.");
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (newFilters) => {
    if (newFilters.clearAll) {
      setFilters({ sort: 'price_asc', times: [] });
      return;
    }
    setFilters(prev => ({ ...prev, ...newFilters }));
  };

  const handleSelectFlight = (flight) => {
    if (searchParams?.tripType === 'round-trip' && bookingStage === 'outbound') {
      // Đã chọn xong chiều đi, chuyển sang chiều về
      setOutboundFlight(flight);
      setBookingStage('return');
      
      // Load chuyến về
      const returnParams = {
        ...searchParams,
        departure: searchParams.arrival,
        arrival: searchParams.departure,
        date: searchParams.returnDate
      };
      setSearchParams(returnParams);
      fetchFlights(returnParams);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      // Đã chọn xong chiều về (hoặc là vé một chiều)
      if (searchParams?.tripType === 'round-trip') {
        localStorage.setItem('selected_flights', JSON.stringify([outboundFlight, flight]));
      } else {
        localStorage.setItem('selected_flights', JSON.stringify([flight]));
      }
      localStorage.removeItem('selected_flight'); // dọn dẹp biến cũ
      navigate('/seat-selection'); 
    }
  };

  const getFilteredAndSortedFlights = () => {
    let result = [...flights];

    // Lọc theo giờ bay
    if (filters.times?.length > 0) {
      result = result.filter(f => {
        const hour = new Date(f.departure_time).getHours();
        if (filters.times.includes('morning') && hour >= 0 && hour < 12) return true;
        if (filters.times.includes('afternoon') && hour >= 12 && hour < 18) return true;
        if (filters.times.includes('evening') && hour >= 18 && hour < 24) return true;
        return false;
      });
    }

    // Sắp xếp
    result.sort((a, b) => {
      if (filters.sort === 'price_asc') {
        const priceA = parseFloat(a.display_price || a.base_price);
        const priceB = parseFloat(b.display_price || b.base_price);
        return priceA - priceB;
      }
      if (filters.sort === 'time_asc') {
        return new Date(a.departure_time) - new Date(b.departure_time);
      }
      return 0;
    });

    return result;
  };

  const displayedFlights = getFilteredAndSortedFlights();

  return (
    <motion.div 
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -15 }}
      transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
      className="min-h-[100dvh] bg-zinc-50 text-zinc-900 pb-24 font-sans relative"
    >
      <Navbar />
      {/* Quầng sáng nền Ambient Glow mềm mại */}
      <div className="absolute top-0 right-1/4 w-[600px] h-[600px] bg-blue-500/5 blur-[150px] rounded-full pointer-events-none -z-10" />
      <div className="absolute bottom-1/4 left-10 w-[400px] h-[400px] bg-indigo-500/5 blur-[120px] rounded-full pointer-events-none -z-10" />

      {/* Hero Header Minimal */}
      <header className="pt-24 pb-16 px-6 md:px-12 max-w-7xl mx-auto relative z-10">
        {/* Quay lại trang chủ */}
        <div className="mb-8 -ml-3">
          <BackButton />
        </div>
        <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tighter leading-none mb-6 bg-gradient-to-r from-zinc-900 via-zinc-800 to-blue-900 bg-clip-text text-transparent">
          {searchParams?.tripType === 'round-trip' 
            ? (bookingStage === 'outbound' ? 'Chọn chuyến bay đi' : 'Chọn chuyến bay về')
            : 'Chuyến bay của bạn'}
        </h1>
        <p className="text-base text-zinc-500 max-w-[65ch] leading-relaxed">
          Tìm kiếm và chọn lựa những chuyến bay tốt nhất. Giá vé hiển thị đã bao gồm thuế và phí dịch vụ.
        </p>
      </header>

      {/* Main Layout: Flex container song song (cần items-start để aside sticky hoạt động) */}
      <main className="px-6 md:px-12 max-w-7xl mx-auto flex flex-col md:flex-row items-start gap-12 relative z-10">
        
        {/* Left: Sticky Filters */}
        <FlightFilterSidebar filters={filters} onFilterChange={handleFilterChange} />

        {/* Right: Flight List */}
        <div className="flex-1 w-full">
          {/* Top Bar Summary */}
          <div className="flex items-center justify-between mb-8 pb-4 border-b border-zinc-200">
            <span className="text-sm font-medium text-zinc-500">
              Hiển thị <strong className="text-zinc-900">{displayedFlights.length}</strong> kết quả
            </span>
          </div>

          {loading ? (
            <div className="py-24 flex flex-col items-center justify-center gap-4 opacity-50 text-zinc-500">
              <MagnifyingGlass className="animate-pulse" size={32} />
              <p className="text-sm font-medium">Đang tìm chuyến bay...</p>
            </div>
          ) : error ? (
            <div className="py-24 text-center">
              <p className="text-red-500 font-medium">{error}</p>
            </div>
          ) : displayedFlights.length === 0 ? (
            <div className="py-32 flex flex-col items-center justify-center text-zinc-400">
              <AirplaneLanding size={48} weight="duotone" className="mb-4 opacity-50 text-blue-500 animate-bounce" />
              <p className="text-sm font-medium">Không tìm thấy chuyến bay nào phù hợp với bộ lọc.</p>
            </div>
          ) : (
            <ul className="flex flex-col gap-6 w-full">
              {displayedFlights.map((flight) => (
                <FlightCard 
                  key={flight.id} 
                  flight={flight} 
                  onSelect={handleSelectFlight} 
                />
              ))}
            </ul>
          )}
        </div>

      </main>
    </motion.div>
  );
}